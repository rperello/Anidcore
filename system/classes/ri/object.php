<?php

class Ri_Object implements ArrayAccess {

    /**
     * @var array 
     */
    protected $vars;

    public function __construct($vars) {
        $this->vars = (array) $vars;
    }

    public function get($key = null, $default = false, $filter = null) {
        if ($key === null) {
            return $this->vars;
        } else {
            return ri_arr_value($this->vars, $key, $default, $filter);
        }
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __isset($name) {
        return isset($this->vars[$name]);
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

    public function toArray() {
        return $this->vars;
    }

    public function toObject() {
        return (object) $this->vars;
    }

    public function replace($vars) {
        $this->vars = (array) $vars;
    }

}