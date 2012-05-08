<?php

class Ac_Global implements ArrayAccess {

    /**
     * @var Global variable name 
     */
    protected $name;

    public function __construct($name) {
        $this->name = $name;
        if (!isset($GLOBALS[$this->name]))
            $GLOBALS[$this->name] = array();
    }

    public function __isset($name) {
        return isset($GLOBALS[$this->name][$name]);
    }

    public function __($name = null, $default = false, $filter = null) {
        if ($name === null) {
            return $GLOBALS[$this->name];
        } else {
            return ac_arr_value($GLOBALS[$this->name], $name, $default, $filter);
        }
    }

    public function __get($name) {
        return $this->__($name);
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

    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        return $this->__unset($offset);
    }

    public function clear() {
        $GLOBALS[$this->name] = array();
    }

    public function import($arr) {
        $GLOBALS[$this->name] = (array) $arr;
    }

    public function export() {
        return $GLOBALS[$this->name];
    }

}