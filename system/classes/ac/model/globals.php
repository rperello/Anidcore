<?php

class Ac_Model_Globals implements ArrayAccess {

    /**
     * @var Global variable name 
     */
    protected $name;

    public function __construct($name) {
        $this->name = $name;
        if (!isset($GLOBALS[$this->name]))
            $GLOBALS[$this->name] = array();
    }

    public function replace(array $values) {
        $GLOBALS[$this->name] = $values;
    }

    public function &getArray() {
        return $GLOBALS[$this->name];
    }

    public function clear() {
        $GLOBALS[$this->name] = array();
    }

    public function __isset($name) {
        return isset($GLOBALS[$this->name][$name]);
    }

    public function &__get($name) {
        return $this->__isset($name) ? $GLOBALS[$this->name][$name] : false;
    }

    public function __set($name, $value) {
        $GLOBALS[$this->name][$name] = $value;
    }

    public function __unset($name) {
        if (isset($GLOBALS[$this->name][$name])) {
            unset($GLOBALS[$this->name][$name]);
            return true;
        }else
            return false;
    }

    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    public function &offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        return $this->__unset($offset);
    }

}