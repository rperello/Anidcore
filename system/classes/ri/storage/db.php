<?php

define("RI_DB_FETCH_ASSOC_FIRST", 2220);
define("RI_DB_FETCH_OBJ_FIRST", 2221);
define("RI_DB_EVENT_ERROR", 3330);
define("RI_DB_EVENT_INSERT", 3331);
define("RI_DB_EVENT_SELECT", 3332);
define("RI_DB_EVENT_UPDATE", 3333);
define("RI_DB_EVENT_DELETE", 3334);
define("RI_DB_EVENT_TRUNCATE", 3335);

class Ri_Storage_Db {

    /**
     *
     * @var PDO
     */
    protected $pdo = NULL;

    /**
     *
     * @var string
     */
    protected $instance_name = NULL;

    /**
     *
     * @var string
     */
    protected $config = NULL;

    /**
     *
     * @var string
     */
    protected $options = NULL;

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

    public static function init($dbconf) {
        if (is_array($dbconf)) {
            if (isset($dbconf["schema"]) && $dbconf["enabled"]) { //single connection
                new Ri_Storage_Db($dbconf["instance"], $dbconf, $dbconf["options"]);
            } elseif (isset($dbconf[0])) { // multiple DBs
                foreach ($dbconf as $i => $conf) {
                    if (isset($dbconf["enabled"]) && ($dbconf["enabled"] == true)) {
                        new Ri_Storage_Db($conf["instance"], $conf, $conf["options"]);
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
        return isset($this->config["prefix"]) ? $this->config["prefix"] : NULL;
    }

    /**
     * Connection DSN string
     * @return string 
     */
    public function dsn() {
        return isset($this->config["dsn"]) ? $this->config["dsn"] : NULL;
    }

    public function connect() {
        if ($this->pdo == NULL) {
            $this->pdo = new PDO($this->config["dsn"], $this->config["username"], $this->config["password"], $this->options);

            if (preg_match("/mysql/i", $this->config["driver"]) > 0) {
                $this->pdo->exec("SET NAMES '{$this->config["charset"]}' COLLATE '{$this->config["collate"]}'");
            }
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
     * @return Ri_Storage_Db
     */
    public static function getConnection($name) {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $name Instance name
     * @param Ri_Storage_Db The instance
     * @param array $options [Optional] PDO Connection Options
     */
    public static function setConnection($name, Ri_Storage_Db $connection) {
        self::$instances[$name] = $connection;
    }

    /**
     *
     * @return Ri_Storage_Db
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
    public static function clearVarcache($hash = NULL) {
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
        Ri::timerStart();

        $this->lastRowCount = 0;
        $this->connect();
        $this->lastRowCount = $this->pdo->exec($statement);
        if ($this->lastRowCount > 0)
            self::clearVarcache();
        self::$queryCount++;

        if (!$this->isError()) {
            $this->logSuccess($statement);
        } else {
            $this->logError($statement);
        }

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
        Ri::timerStart();

        $statement = trim($statement);
        $this->lastRowCount = 0;
        $args = func_get_args();
        $hash = sha1($this->instance_name . "," . serialize($args));
        $fetch_all = true;

        if (isset($args[1])) {
            if (($args[1] == RI_DB_FETCH_ASSOC_FIRST) || ($args[1] == RI_DB_FETCH_OBJ_FIRST))
                $fetch_all = false;
            if ($args[1] == RI_DB_FETCH_ASSOC_FIRST)
                $args[1] = PDO::FETCH_ASSOC;
            elseif ($args[1] == RI_DB_FETCH_OBJ_FIRST)
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
            return self::$varcache[$hash];
        } else {
            $this->logError($statement);
        }
        return false;
    }

    protected function _logSuccess($statement, $data = array()) {
        self::$log[] = array(self::$queryCount . " ", $statement, $this->lastRowCount() . " ", $data, Ri::timerStop(), 'OK', $this->instance_name);
    }

    protected function _logError($statement) {
        self::$log[] = array(self::$queryCount . " ", $statement, $this->lastRowCount() . " ", NULL, Ri::timerStop(),
            $this->pdo->errorInfo(), $this->instance_name);

        $content = "#" . self::$queryCount . " [ERROR]\n";
        $content .= print_r(array($statement, $this->pdo->errorInfo()), true);

        //ri_log($content, "mysql_errors.log", "file");
    }

    public function uniqueStringFrom($table, $field, $andWhere = "", $length = 32, $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        $str = false;
        while ($str == false) {
            $str = ri_str_random($length, $chars);
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

            if (($value !== NULL) && ($value !== "") && (strtoupper($value) != "NULL")) {
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
    public function update($table, array $data, $where = NULL, $limit = NULL) {
        if (!empty($where)) {
            $where = "WHERE " . $where;
        }
        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        $setters = array();
        foreach ($data as $field => $value) {
            $value = str_escape(stripslashes($value));
            if (($value === NULL) || ($value === "") || (strtoupper($value) == "NULL")) {
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
    public function delete($from, $where = NULL, $limit = NULL) {
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
    public function findWhere($where, $from, $orderBy = NULL, $limit = NULL, $select = NULL) {
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
    public function findAll($from, $orderBy = NULL, $limit = NULL, $select = NULL) {
        $args = func_get_args();
        array_unshift($args, NULL); //  where
        return call_user_func_array(array($this, "findWhere"), $args);
    }

    /**
     *
     * @param string $where
     * @param string $from Table name
     * @param string $select
     * @param int $fetch_style Apricore_Db::FETCH_OBJ_FIRST or Apricore_Db::FETCH_FIRST_ARRAY
     * @param mixed  $_arg,...
     * Extra args, like in PDOStatement::fetch or fetchAll
     * 
     * <b>Possible arguments:</b><br>
     * $cursor_orientation or $column_index,<br>
     * $cursor_offset or $constructor_args
     * 
     * @return mixed or boolean false 
     */
    public function findOne($where, $from, $select = NULL, $fetch_style = RI_DB_FETCH_ASSOC_FIRST) {
        $args = array_slice(func_get_args(), 2);

        if (func_num_args() < 4)         //  fetch_style
            array_push($args, RI_DB_FETCH_ASSOC_FIRST);

        if (func_num_args() < 3) {
            array_unshift($args, NULL); //  select
        }
        array_unshift($args, 1);  //limit
        array_unshift($args, NULL); //orderBy
        array_unshift($args, $from);
        array_unshift($args, $where);

        if (!in_array($args[5], array(RI_DB_FETCH_ASSOC_FIRST, RI_DB_FETCH_OBJ_FIRST))) {
            $args[5] = RI_DB_FETCH_ASSOC_FIRST;
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
    public function findBy($field, $value, $from, $orderBy = NULL, $limit = NULL, $select = NULL) {
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
    public function search($value, $fields, $strict_compare = true, $from = NULL, $orderBy = NULL, $limit = NULL, $select = NULL) {
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