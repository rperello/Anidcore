<?php

class Ac_Array implements ArrayAccess {

    /**
     * @var array 
     */
    protected $vars;

    public function __construct($vars = array()) {
        $this->vars = (array) $vars;
    }

    public function __isset($name) {
        return isset($this->vars[$name]);
    }

    public function __get($name) {
        return $this->__($name);
    }

    public function __($name = null, $default = false, $filter = null) {
        if ($name === null) {
            return $this->vars;
        } else {
            return ac_arr_value($this->vars, $name, $default, $filter);
        }
    }

    public function __set($name, $value) {
        $this->vars[$name] = $value;
    }

    public function __unset($name) {
        if (isset($this->vars[$name])) {
            unset($this->vars[$name]);
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
        $this->vars = array();
    }

    public function import($arr) {
        $this->vars = (array) $arr;
    }

    public function export() {
        return $this->vars;
    }

}