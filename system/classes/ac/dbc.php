<?php

class Ac_Dbc {

    const FETCH_ASSOC_FIRST = 3000;
    const FETCH_OBJ_FIRST = 3001;
    const HOOK_BEFORE_CONNECT = "ac.db_before_connect";
    const HOOK_BEFORE_SUCCESS = "ac.db_before_success";
    const HOOK_BEFORE_ERROR = "ac.db_before_error";
    const HOOK_BEFORE_EXECUTE = "ac.db_before_execute";
    const HOOK_BEFORE_SELECT = "ac.db_before_select";
    const HOOK_ON_CONNECT = "ac.db_on_connect";
    const HOOK_ON_SUCCESS = "ac.db_on_success";
    const HOOK_ON_ERROR = "ac.db_on_error";
    const HOOK_ON_EXECUTE = "ac.db_on_execute";
    const HOOK_ON_SELECT = "ac.db_on_select";

    /**
     *
     * @var PDO
     */
    protected $pdo = null;

    /**
     *
     * @var string
     */
    protected $instance_name = null;

    /**
     *
     * @var string
     */
    protected $config = null;

    /**
     *
     * @var string
     */
    protected $options = null;

    /**
     *
     * @var int
     */
    protected $lastRowCount = 0;

    /**
     *
     * @var int
     */
    protected static $queryCount = 0;

    /**
     *
     * @var array
     */
    protected static $instances = array();

    /**
     *
     * @var string or false
     */
    protected static $active_instance = false;
    protected static $log = array(array('#', 'Statement', 'Rows', 'Result', 'Time', 'Status', 'Instance'));

    /**
     *
     * @var array
     */
    protected static $varcache = array();

    /**
     *
     * @param array $config Single or multiple database configurations. Each one
     * containing:
     * 
     * [enabled] boolean <i>Whether connection is enabled or not</i> <br>
     * [instance] string  <i>Instance name (informational, for identifying it later)</i><br>
     * [driver] string  <i>PDO driver</i><br>
     * [host] string  <i>DB connection host</i><br>
     * [port] string  <i>DB connection port</i><br>
     * [schema] string  <i>DB name</i><br>
     * [prefix] string  <i>DB prefix (informational only)</i><br>
     * [username] string  <i>DB username</i><br>
     * [password] string  <i>DB password</i><br>
     * [charset] string  <i>DB connection charset</i><br>
     * [collate] string  <i>DB database collation</i><br>
     * [options] array  <i>PDO options</i><br>
     * [autoconnect] boolean <i>If false, lazy connection will be used (recommended)</i><br>
     */
    public static function init($config) {
        if (is_array($config)) {
            if (isset($config["schema"]) && $config["enabled"]) { //single connection
                new self($config["instance"], $config, $config["options"]);
            } elseif (isset($config[0])) { // multiple DBs
                foreach ($config as $i => $conf) {
                    if (isset($config["enabled"]) && ($config["enabled"] == true)) {
                        new self($conf["instance"], $conf, $conf["options"]);
                    }
                }
            }
        }
    }

    public function __construct($instanceName, array $config, $options = array()) {
        $config["dsn"] = "{$config["driver"]}:host={$config["host"]};port={$config["port"]};dbname={$config["schema"]}";
        $this->config = $config;
        $this->instance_name = $instanceName;

        if (!isset($options[PDO::ATTR_DEFAULT_FETCH_MODE]))
            $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;

        $this->options = $options;

        self::$instances[$instanceName] = $this;

        if (empty(self::$active_instance)) {
            self::$active_instance = $instanceName;
        }

        if ($config["autoconnect"]) {
            $this->connect();
        }
    }

    public function prefix() {
        return isset($this->config["prefix"]) ? $this->config["prefix"] : null;
    }

    /**
     * Connection DSN string
     * @return string 
     */
    public function dsn() {
        return isset($this->config["dsn"]) ? $this->config["dsn"] : null;
    }

    public function connect() {
        if ($this->pdo == null) {
            $this->config = Ac::hookApply(self::HOOK_BEFORE_CONNECT, $this->config);
            $this->pdo = new PDO($this->config["dsn"], $this->config["username"], $this->config["password"], $this->options);

            if (preg_match("/mysql/i", $this->config["driver"]) > 0) {
                $this->pdo->exec("SET NAMES '{$this->config["charset"]}' COLLATE '{$this->config["collate"]}'");
            }
            Ac::hookApply(self::HOOK_ON_CONNECT, $this);
            return true;
        }
        return false;
    }

    public function __call($name, $arguments) {
        $this->connect();
        return call_user_func_array(array($this->pdo, $name), $arguments);
    }

    public function getLog() {
        return self::$log;
    }

    /**
     * Instance name
     * @return string
     */
    public function name() {
        return $this->instance_name;
    }

    /**
     * Database name
     * @return string
     */
    public function schemaName() {
        return $this->config["schema"];
    }

    /**
     * Host name
     * @return string
     */
    public function hostName() {
        return $this->config["host"] . ":" . $this->config["port"];
    }

    /**
     * SQL driver name
     * @return string
     */
    public function driverName() {
        return $this->config["driver"];
    }

    /**
     *
     * @param string $name Instance name
     * @return Ac_Dbc
     */
    public static function getConnection($name = null) {
        if (empty($name))
            return self::getActiveConnection();

        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $name Instance name
     * @param Ac_Dbc The instance
     * @param array $options [Optional] PDO Connection Options
     */
    public static function setConnection($name, Ac_Dbc $connection) {
        self::$instances[$name] = $connection;
    }

    /**
     *
     * @return Ac_Dbc
     */
    public static function getActiveConnection() {
        if (self::$active_instance == false)
            return false;
        if (isset(self::$instances[self::$active_instance])) {
            return self::$instances[self::$active_instance];
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $name Instance name
     * @return bool 
     */
    public static function setActiveConnection($name) {
        if (isset(self::$instances[$name])) {
            self::$active_instance = $name;
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return bool
     */
    public static function hasConnections() {
        return (count(self::$instances) > 0) && (self::$active_instance != false);
    }

    /**
     * 
     */
    public static function clearVarcache($hash = null) {
        if (!empty($hash)) {
            unset(self::$varcache[$hash]);
        }else
            self::$varcache = array();
    }

    /**
     *
     * @return bool
     */
    public function isError() {
        return preg_match("/^0+$/", $this->errorCode()) == false;
    }

    /**
     *
     * @return int
     */
    public function lastRowCount() {
        return $this->lastRowCount;
    }

    /**
     *
     * @return int
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     *
     * @return int
     */
    public function queryCount() {
        return self::$queryCount;
    }

    /**
     *
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     *
     * @return bool
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }

    /**
     *
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     *
     * @return bool
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * 
     * @return PDO
     */
    public function pdo() {
        return $this->pdo;
    }

    /**
     *
     * @param string $statement
     * @return int or boolean false
     */
    public function exec($statement) {
        Ac::timerStart();
        $statement = Ac::hookApply(self::HOOK_BEFORE_EXECUTE, array($this, $statement));
        $statement = $statement[1];

        $this->lastRowCount = 0;
        $this->connect();
        $this->beginTransaction();
        $this->lastRowCount = $this->pdo->exec($statement);
        if ($this->lastRowCount > 0)
            self::clearVarcache();
        self::$queryCount++;

        if (!$this->isError()) {
            $this->commit();
            Ac::hookApply(self::HOOK_ON_SUCCESS, array('exec', $this, $statement));
            $this->logSuccess($statement);
        } else {
            $this->rollback();
            Ac::hookApply(self::HOOK_ON_ERROR, array('exec', $this, $statement));
            $this->logError($statement);
        }


        Ac::hookApply(self::HOOK_ON_EXECUTE, array($this, $statement, $this->lastRowCount));

        return $this->lastRowCount;
    }

    /**
     *
     * @param string $statement
     * @param mixed $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $fetch_style,<br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false
     */
    public function query($statement) {
        Ac::timerStart();
        $result = false;
        $statement = Ac::hookApply(self::HOOK_BEFORE_SELECT, array($this, $statement));
        $statement = $statement[1];

        $statement = trim($statement);
        $this->lastRowCount = 0;
        $args = func_get_args();
        $hash = sha1($this->instance_name . "," . serialize($args));
        $fetch_all = true;

        if (isset($args[1])) {
            if (($args[1] == self::FETCH_ASSOC_FIRST) || ($args[1] == self::FETCH_OBJ_FIRST))
                $fetch_all = false;
            if ($args[1] == self::FETCH_ASSOC_FIRST)
                $args[1] = PDO::FETCH_ASSOC;
            elseif ($args[1] == self::FETCH_OBJ_FIRST)
                $args[1] = PDO::FETCH_OBJ;
        }else {
            $args[1] = $this->options[PDO::ATTR_DEFAULT_FETCH_MODE];
        }

        $this->connect();
        $pdoStatement = $this->pdo->query($statement);

        self::$queryCount++;

        if (!$this->isError()) {
            if (!isset(self::$varcache[$hash])) {
                array_shift($args);
                $records = call_user_func_array(array($pdoStatement, "fetchAll"), $args);
                $this->lastRowCount = count($records);
                if (!$fetch_all) {
                    if (count($records) > 0) {
                        $records = $records[0];
                        $this->lastRowCount = 1;
                    } else {
                        $records = false;
                        $this->lastRowCount = 0;
                    }
                } else {
                    $this->lastRowCount = count($records);
                }
                self::$varcache[$hash] = $records;
                $this->logSuccess($statement, $records);
            }
            Ac::hookApply(self::HOOK_ON_SUCCESS, array('select', $this, $statement));
            $result = self::$varcache[$hash];
        } else {
            Ac::hookApply(self::HOOK_ON_ERROR, array('select', $this, $statement));
            $this->logError($statement);
        }

        Ac::hookApply(self::HOOK_ON_SELECT, array($this, $statement, $result));

        return $result;
    }

    protected function _logSuccess($statement, $data = array()) {
        self::$log[] = array(self::$queryCount . " ", $statement, $this->lastRowCount() . " ", $data, Ac::timerStop(), 'OK', $this->instance_name);
    }

    protected function _logError($statement) {
        self::$log[] = array(self::$queryCount . " ", $statement, $this->lastRowCount() . " ", null, Ac::timerStop(),
            $this->pdo->errorInfo(), $this->instance_name);

        $content = "#" . self::$queryCount . " [ERROR]\n";
        $content .= print_r(array($statement, $this->pdo->errorInfo()), true);

        //ac_log($content, "mysql_errors.log", "file");
    }

    public function uniqueStringFrom($table, $field, $andWhere = "", $length = 32, $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        $str = false;
        while ($str == false) {
            $str = ac_str_random($length, $chars);
            if ($this->findOne("($field='$str') $andWhere", $table)) {
                $str = false;
            }
        }
        return $str;
    }

    /**
     *
     * @param string $into Table name
     * @param array $data key-value pairs
     * @return int or boolean false
     */
    public function insert($into, array $data) {
        $fields = array();
        $values = array();
        foreach ($data as $field => $value) {
            $value = str_escape(stripslashes($value));

            if (($value !== null) && ($value !== "") && (strtoupper($value) != "null")) {
                $fields[] = "`" . $field . "`";
                $values[] = "'" . $value . "'";
            } else {
                $fields[] = "`" . $field . "`";
                $values[] = "null";
            }
        }
        if (count($fields) == 0) {
            $this->exec("INSERT INTO `{$into}` VALUES ()");
        } else {
            $fields = implode(", ", $fields);
            $values = implode(", ", $values);
            $this->exec("INSERT INTO `{$into}` ({$fields}) VALUES ({$values})");
        }
        return $this->lastInsertId();
    }

    /**
     *
     * @param string $table
     * @param array $data key-value pairs
     * @param string $where
     * @param string $limit
     * @return bool
     */
    public function update($table, array $data, $where = null, $limit = null) {
        if (!empty($where)) {
            $where = "WHERE " . $where;
        }
        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        $setters = array();
        foreach ($data as $field => $value) {
            $value = str_escape(stripslashes($value));
            if (($value === null) || ($value === "") || (strtoupper($value) == "null")) {
                $setters[] = "`{$field}`=null ";
            }else
                $setters[] = "`{$field}`='{$value}' ";
        }
        if (count($setters) > 0) {
            $setters = implode(", ", $setters);
            $this->exec("UPDATE `{$table}` SET {$setters} {$where} {$limit}");
            return $this->isError() == false;
        }else
            return false;
    }

    /**
     *
     * @param string $from Table name
     * @param string $where
     * @param string $limit
     * @return bool
     */
    public function delete($from, $where = null, $limit = null) {
        if (!empty($where)) {
            $where = "WHERE " . $where;
        }
        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        $this->exec("DELETE FROM `{$from}` {$where} {$limit}");
        if ($this->lastRowCount > 0) {
            return true;
        }else
            return false;
    }

    /**
     *
     * @param string $table
     * @return int or boolean false 
     */
    public function truncate($table) {
        if ($this->driverName() == "sqlite") {
            $this->exec("DELETE FROM `{$table}`; VACUUM;");
        }else
            $this->exec("TRUNCATE TABLE `{$table}`");

        self::clearVarcache();

        return $this->lastRowCount();
    }

    /**
     *
     * @param string $where
     * @param string $from
     * @param string $orderBy
     * @param string $limit
     * @param string $select
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $fetch_style,<br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false
     */
    public function findWhere($where, $from, $orderBy = null, $limit = null, $select = null) {
        if (empty($select))
            $select = "SELECT *";
        if (!empty($where))
            $where = "WHERE $where";
        if (!empty($orderBy))
            $orderBy = "ORDER BY " . $orderBy;
        if (!empty($limit))
            $limit = "LIMIT " . $limit;

        $statement = " {$select} FROM {$from} {$where} {$orderBy} {$limit}";

        if (func_num_args() > 5) {
            $args = array_slice(func_get_args(), 5);
        }else
            $args = array();

        array_unshift($args, $statement);

        return call_user_func_array(array($this, "query"), $args);
    }

    /**
     *
     * @param string $from Table name
     * @param string $orderBy
     * @param string $limit
     * @param string $select
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $fetch_style,<br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false 
     */
    public function findAll($from, $orderBy = null, $limit = null, $select = null) {
        $args = func_get_args();
        array_unshift($args, null); //  where
        return call_user_func_array(array($this, "findWhere"), $args);
    }

    /**
     *
     * @param string $where
     * @param string $from Table name
     * @param string $select
     * @param int $fetch_style Anidcore_Db::FETCH_OBJ_FIRST or Anidcore_Db::FETCH_FIRST_ARRAY
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false 
     */
    public function findOne($where, $from, $select = null, $fetch_style = self::FETCH_ASSOC_FIRST) {
        $args = array_slice(func_get_args(), 2);

        if (func_num_args() < 4)         //  fetch_style
            array_push($args, self::FETCH_ASSOC_FIRST);

        if (func_num_args() < 3) {
            array_unshift($args, null); //  select
        }
        array_unshift($args, 1);  //limit
        array_unshift($args, null); //orderBy
        array_unshift($args, $from);
        array_unshift($args, $where);

        if (!in_array($args[5], array(self::FETCH_ASSOC_FIRST, self::FETCH_OBJ_FIRST))) {
            $args[5] = self::FETCH_ASSOC_FIRST;
        }
        return call_user_func_array(array($this, "findWhere"), $args);
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @param string $from
     * @param string $orderBy
     * @param string $limit
     * @param string $select
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $fetch_style,<br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false
     */
    public function findBy($field, $value, $from, $orderBy = null, $limit = null, $select = null) {
        $args = array_slice(func_get_args(), 2);
        array_unshift($args, "`{$field}`='{$value}'");
        return call_user_func_array(array($this, "findWhere"), $args);
    }

    /**
     *
     * @param string $value The value to search
     * @param mixed $fields Fields to search in. <br>String containing comma-separated field names or array of field names
     * @param bool $strict_compare Strict comparision or use LIKE
     * @param string $from
     * @param string $orderBy
     * @param string $limit
     * @param string $select
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $fetch_style,<br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false
     */
    public function search($value, $fields, $strict_compare = true, $from = null, $orderBy = null, $limit = null, $select = null) {
        $where = array();
        if (is_string($fields))
            $fields = explode(",", $fields);
        foreach ($fields as $field) {
            if ($strict_compare) {
                $where[] = "`{$field}`='{$value}'";
            } else {
                $where[] = "LOWER(`{$field}`) LIKE LOWER('%{$value}%')";
            }
        }
        $where = implode(" OR ", $where);


        $args = array_slice(func_get_args(), 3);
        array_unshift($args, $where);

        return call_user_func_array(array($this, "findWhere"), $args);
    }

    /**
     *
     * @param string $field
     * @param string $table
     * @return mixed or boolean false 
     */
    public function findDuplicated($field, $table) {
        return $this->query("COUNT(*) as repeats, `$field` FROM `$table` GROUP BY `$field` HAVING COUNT(*) > 1");
    }

}