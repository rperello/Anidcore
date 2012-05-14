<?php

class Ac_Storage_Pdo {

    const FETCH_ASSOC_FIRST = 2000;
    const FETCH_OBJ_FIRST = 2001;

    /**
     * 
     * @var array[Ac_Storage_Pdo]
     */
    protected static $connections = array();

    /**
     * 
     * @var string|false
     */
    protected static $active_connection = false;

    /**
     * 
     * @var int
     */
    protected $num_operations = 0;

    /**
     * 
     * @var PDO
     */
    protected $pdo = null;

    /**
     * 
     * @var array
     */
    protected $config = null;

    /**
     * 
     * @var array
     */
    protected $cache = array();

    /**
     *
     * @var array 
     */
    protected $log = array(array('#', 'Statement', 'Rows', 'Result', 'Time', 'Status', 'Instance'));

    /**
     * 
     * @var int
     */
    protected $last_row_count = 0;

    /**
     *
     * @param array $configs Single or multiple database configurations. Each one
     * containing:
     * 
     * [enabled] boolean <i>Whether connection is enabled or not</i> <br>
     * [instance] string  <i>Instance name (informational, for identifying it later)</i><br>
     * [driver] string  <i>PDO driver</i><br>
     * [host] string  <i>DB connection host</i><br>
     * [port] string  <i>DB connection port</i><br>
     * [dbname] string  <i>DB name</i><br>
     * [prefix] string  <i>DB prefix (informational only)</i><br>
     * [username] string  <i>DB username</i><br>
     * [password] string  <i>DB password</i><br>
     * [charset] string  <i>DB connection charset</i><br>
     * [collate] string  <i>DB database collation</i><br>
     * [options] array  <i>PDO options</i><br>
     * [autoconnect] boolean <i>If false, lazy connection will be used (recommended)</i><br>
     */
    public static function __init(array $configs) {
        if (!empty(static::$connections)) {
            unset(static::$connections);
            static::$active_connection = false;
        }

        if (is_array($configs)) {
            if (isset($configs["dbname"]) && $configs["enabled"]) { //single connection
                if (isset($configs["enabled"]) && ($configs["enabled"] == true)) {
                    new self($configs);
                }
            } elseif (isset($configs[0])) { // multiple DBs
                foreach ($configs as $i => $conf) {
                    if (isset($conf["enabled"]) && ($conf["enabled"] == true)) {
                        new self($conf);
                    }
                }
            }
        }
    }

    /**
     *
     * @return boolean
     */
    public static function hasConnections() {
        return (count(static::$connections) > 0) && (static::$active_connection != false);
    }

    /**
     *
     * @param string $name Instance name
     * @param Ac_Storage_Pdo The instance
     */
    public static function setConnection($name, Ac_Storage_Pdo $connection) {
        static::$connections[$name] = $connection;
    }

    /**
     *
     * @param string $name Instance name
     * @return Ac_Storage_Pdo
     */
    public static function getConnection($name = null) {
        if (empty($name)) {
            if (static::$active_connection == false)
                return false;
            if (isset(static::$connections[static::$active_connection])) {
                return static::$connections[static::$active_connection];
            }
        }

        if (isset(static::$connections[$name])) {
            return static::$connections[$name];
        }
        return false;
    }

    /**
     *
     * @param string $name Instance name
     * @return boolean
     */
    public static function setActiveConnection($name) {
        if (isset(static::$connections[$name])) {
            static::$active_connection = $name;
            return true;
        } else {
            return false;
        }
    }

    public function __construct(array $config) {
        // DSN string
        $config["dsn"] = "{$config["driver"]}:host={$config["host"]};port={$config["port"]};dbname={$config["dbname"]}";

        $config = array_merge(array(
            "enabled" => true,
            "instance" => "default",
            "prefix" => null,
            "charset" => "utf8",
            "collate" => "utf8_general_ci",
            "options" => array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ),
            "autoconnect" => false,
                ), $config);

        // Default fetch mode
        if (!isset($config["options"][PDO::ATTR_DEFAULT_FETCH_MODE]))
            $config["options"][PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;

        $this->config = $config;

        // Set instance
        static::$connections[$config["instance"]] = $this;

        if (empty(static::$active_connection)) {
            static::$active_connection = $config["instance"];
        }

        // Autoconnect?
        if ($config["autoconnect"]) {
            $this->connect();
        }
    }

    /**
     *
     * @param string $name
     * @return mixed|null 
     */
    public function getConfig($name = null) {
        if (empty($name))
            return $this->config;

        if (isset($this->config[$name])) {
            return $this->config[$name];
        }else
            return null;
    }

    /**
     * 
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    public function __call($name, $arguments) {
        $this->connect();
        return call_user_func_array(array($this->pdo, $name), $arguments);
    }

    /**
     *
     * @return boolean 
     */
    public function connect() {
        if (empty($this->pdo)) {
            $this->config = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, $this->config);
            $this->pdo = new PDO($this->config["dsn"], $this->config["username"], $this->config["password"], $this->config["options"]);

            if (preg_match("/mysql/i", $this->config["driver"]) > 0) {
                $this->pdo->exec("SET NAMES '{$this->config["charset"]}' COLLATE '{$this->config["collate"]}'");
            }
            Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, $this);
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isError() {
        return preg_match("/^0+$/", $this->pdo->errorCode()) == false;
    }

    /**
     *
     * @return int
     */
    public function lastRowCount() {
        return $this->last_row_count;
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
    public function getNumOperations() {
        return $this->$num_operations;
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
     * @return boolean
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }

    /**
     *
     * @return boolean
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     *
     * @return boolean
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * 
     */
    public function clearCache() {
        unset($this->cache);
        $this->cache = array();
    }

    public function getLog() {
        return $this->log;
    }

    protected function log($statement, $status, $data = null) {
        $this->log[] = array($this->num_operations . " ", $statement, $this->lastRowCount() . " ", $data, Ac::timerStop(),
            $status, $this->config["instance"]);
    }

    public function query($statement, $fetch_style = null) {
        Ac::timerStart();
        $this->connect();

        list($_this, $statement, $fetch_style) = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array($this, trim($statement), $fetch_style));

        if (empty($fetch_style))
            $fetch_style = $this->config["options"][PDO::ATTR_DEFAULT_FETCH_MODE];

        $this->last_row_count = 0;
        $hash = sha1($statement . '/' . $fetch_style);
        $result = false;

        if (isset($this->cache[$hash])) {
            $result = $this->cache[$hash];
        } else {
            $fetch_one = (($fetch_style == static::FETCH_ASSOC_FIRST) || ($fetch_style == static::FETCH_OBJ_FIRST));

            if ($fetch_style == static::FETCH_ASSOC_FIRST)
                $fetch_style = PDO::FETCH_ASSOC;
            elseif ($fetch_style == static::FETCH_OBJ_FIRST)
                $fetch_style = PDO::FETCH_OBJ;

            $pdoStatement = $this->pdo->query($statement);
            $this->num_operations++;

            if (!$this->isError()) {
                $result = $pdoStatement->fetchAll($fetch_style);
                $this->last_row_count = count($result);

                if ($fetch_one) {
                    if (count($result) > 0)
                        $result = $result[0];
                    else
                        $result = false;
                }

                $this->cache[$hash] = $result;

                $status = "success";
                if ($this->getConfig("log_success"))
                    $this->log($statement, "OK", $result);
            }else {

                $status = "error";
                if ($this->getConfig("log_errors"))
                    $this->log($statement, $this->pdo->errorInfo(), null);
            }
        }


        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, array($this, $statement, $fetch_style, $status, $result));
        return $result;
    }

    public function execute($statement) {
        Ac::timerStart();
        $this->connect();
        list($_this, $statement) = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array($this, trim($statement)));

        $this->last_row_count = 0;
        $this->beginTransaction();
        $this->last_row_count = $this->pdo->exec($statement);

        if ($this->last_row_count > 0)
            $this->clearCache();
        $this->num_operations++;

        if (!$this->isError()) {
            $this->commit();
            $status = "success";
            if ($this->getConfig("log_success"))
                $this->log($statement, "OK", null);
        } else {
            $this->rollback();
            $status = "error";
            if ($this->getConfig("log_errors"))
                $this->log($statement, $this->pdo->errorInfo(), null);
        }

        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, array($this, $statement, $status, $this->last_row_count));

        return $this->last_row_count;
    }

    protected function value($value) {
        $value = ac_str_escape(stripslashes($value));
        if (($value === null) || ($value === "") || (strtoupper($value) == "null")) {
            $value = "null";
        } else {
            $value = "'{$value}'";
        }
        return $value;
    }

    /**
     *
     * @param string $into Table name
     * @param array $data key-value pairs
     * @return int|false The last insert ID
     */
    public function insert($into, array $data) {
        list($_this, $into, $data) = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array($this, $into, $data));
        $into = $r[1];
        $data = $r[2];
        $fields = array();
        $values = array();
        foreach ($data as $field => $value) {
            $fields[] = "`" . $field . "`";
            $values[] = $this->value($value);
        }
        if (count($fields) == 0) {
            $this->exec("INSERT INTO `{$into}` VALUES ()");
        } else {
            $fields = implode(", ", $fields);
            $values = implode(", ", $values);
            $this->exec("INSERT INTO `{$into}` ({$fields}) VALUES ({$values})");
        }
        $result = $this->lastInsertId();
        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, array($this, $into, $data, $result));
        return $result;
    }

    /**
     *
     * @param string $table
     * @param array $data key-value pairs
     * @param string $where
     * @param string $limit
     * @return boolean
     */
    public function update($table, array $data, $where = null, $limit = null) {
        list($_this, $table, $data, $where, $limit) = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array($this, $table, $data, $where, $limit));

        if (!empty($where))
            $where = "WHERE ($where)";

        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        $setters = array();
        foreach ($data as $field => $value) {
            $setters[] = "`{$field}`=" . $this->value($value) . " ";
        }
        if (count($setters) > 0) {
            $setters = implode(", ", $setters);
            $this->exec("UPDATE `{$table}` SET {$setters} {$where} {$limit}");
            $result = !$this->isError();
        } else {
            $result = false;
        }
        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, array($this, $table, $data, $where, $limit, $result));
        return $result;
    }

    /**
     *
     * @param string $from Table name
     * @param string $where
     * @param string $limit
     * @return bool
     */
    public function delete($from, $where = null, $limit = null) {
        list($_this, $from, $where, $limit) = Ac::trigger(__CLASS__ . "_before_" . __FUNCTION__, array($this, $from, $where, $limit));

        if (!empty($where))
            $where = "WHERE ($where)";

        if (!empty($limit)) {
            $limit = "LIMIT " . $limit;
        }

        $this->exec("DELETE FROM `{$from}` {$where} {$limit}");
        $result = ($this->lastRowCount > 0);
        Ac::trigger(__CLASS__ . "_on_" . __FUNCTION__, array($this, $from, $where, $limit, $result));
        return $result;
    }

    public function find($from, $where = null, $orderBy = null, $limit = null, $select = null, $fetch_style = null) {
        if (empty($select))
            $select = "*";
        $select = "SELECT " . $select;

        if (!empty($where))
            $where = "WHERE ($where)";

        if (!empty($orderBy))
            $orderBy = "ORDER BY " . $orderBy;

        if (!empty($limit))
            $limit = "LIMIT " . $limit;

        return $this->query("{$select} FROM {$from} {$where} {$orderBy} {$limit}", $fetch_style);
    }

    public function findBy($from, $field, $value, $orderBy = null, $limit = null, $select = null, $fetch_style = null) {
        return $this->find($from, "`$field`=" . $this->value($value), $orderBy, $limit, $select, $fetch_style);
    }

    public function search($from, $fields, $value, $orderBy = null, $limit = null, $select = null, $fetch_style = null, $exact_match = true, $ternary_operator = "OR") {
        $value = ac_str_escape(stripslashes($value));
        $where = array();
        if (is_string($fields))
            $fields = explode(",", $fields);
        foreach ($fields as $field) {
            if ($exact_match) {
                $where[] = "`{$field}`='{$value}'";
            } else {
                $where[] = "LOWER(`{$field}`) LIKE LOWER('%{$value}%')";
            }
        }

        return $this->find(implode(" $ternary_operator ", $where), $from, $orderBy, $limit, $select, $fetch_style);
    }

}