<?php

/**
 * Active Record Model for Ac_Storage_PDO connections
 * @property int $id bigint(20) Primary key alias
 */
abstract class Ac_Model_Record_PDO extends Ac_Model_Record {

    protected $table;
    protected $primary;

    public function __construct($data = null) {
        $this->table = static::tableName();
        $this->primary = static::constant("PRIMARY_KEY");

        if (is_numeric($data)) {
            $properties = Ac::db()->findBy($this->table, $this->primary, $data, null, 1, null, Ac_Storage_Pdo::FETCH_ASSOC_FIRST);
            if (!$this->exists($properties)) {
                Ac::log()->error(array($data, $this), "This $this->table record does not exist", __FILE__, __LINE__);
                $properties = array();
            }
        } elseif ($data instanceof Ac_Model_Record_PDO) {
            $properties = $data->properties();
        } elseif ($data instanceof stdClass) {
            $properties = (array) $data;
        } else {
            $properties = $data; //array ?
        }
        if (empty($properties))
            $properties = array();
        parent::__construct($properties);
    }

    public function __get_id() {
        return $this->properties[$this->primary];
    }

    public function __set_id($id) {
        $this->properties[$this->primary] = $id;
    }

    public function __set($name, $value) {
        return parent::__set($name, $value);
    }

    public function save() {
        if ($this->exists()) {
            return Ac::db()->update($this->table, $this->properties, $this->primary . "=" . $this->id, 1);
        } elseif (!empty($this->properties)) {
            $id = Ac::db()->insert($this->table, $this->properties);
            if ($id > 0) {
                $this->properties = Ac::db()->findBy($this->table, $this->primary, $id, null, 1, null, Ac_Storage_Pdo::FETCH_ASSOC_FIRST);
                return $id;
            }
        }
        return false;
    }

    public function delete() {
        $result = false;
        if ($this->exists()) {
            if (Ac::db()->delete($this->table, $this->primary . "=" . $this->id, 1)) {
                $result = true;
            }
        }
        $this->properties = array();
        return $result;
    }

    public function exists($properties = null) {
        if ($properties === null)
            $properties = $this->properties;
        return is_array($properties) && isset($properties[$this->primary]) && ($properties[$this->primary] > 0);
    }

    public static function find($where = null, $orderBy = null, $limit = null, $select = null) {
        return static::map(Ac::db()->find(static::tableName(), $where, $orderBy, $limit, $select));
    }

    public static function findBy($field, $value, $orderBy = null, $limit = null, $select = null) {
        return static::map(Ac::db()->findBy(static::tableName(), $field, $value, $orderBy, $limit, $select));
    }

    public static function search($fields, $value, $orderBy = null, $limit = null, $select = null, $exact_match = true, $ternary_operator = "OR") {
        return static::map(Ac::db()->search(static::tableName(), $fields, $value, $orderBy, $limit, $select, null, $exact_match, $ternary_operator));
    }

    public static function map($rows) {
        if (is_array($rows)) {
            foreach ($rows as $i => $r) {
                $rows[$i] = new static($r);
            }
            return $rows;
        }else
            return false;
    }
    
    public static function tableName(){
        return Ac::db()->getConfig("prefix") . static::constant("TABLE_NAME");
    }

}