<?php

/**
 * Active Record Model for Ac_Storage_Database_MongoDB connections
 * @property int $id string Primary key alias
 */
abstract class Ac_Model_Record_MongoDB extends Ac_Model_Record {

    protected $collection;

    public function __construct($data = array()) {
        parent::__construct($data);
    }

    public function __get_id() {
        return $this->properties["_id"];
    }

    public function __set_id($id) {
        $this->properties["_id"] = $id;
    }

    public function __set($name, $value) {
        return parent::__set($name, $value);
    }

    public function save() {
        
    }

    public function delete() {
        
    }

}