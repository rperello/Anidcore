<?php

class Ac_Object implements ArrayAccess {

    /**
     * @var array 
     */
    protected $vars;

    public function __construct($vars) {
        $this->vars = (array) $vars;
    }

    public function val($name = null, $default = false, $filter = null) {
        if ($name === null) {
            return $this->vars;
        } else {
            return ac_arr_value($this->vars, $name, $default, $filter);
        }
    }

    public function __isset($name) {
        $method_name = __FUNCTION__."_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return isset($this->vars[$name]);
    }

    public function __get($name) {
        $method_name = __FUNCTION__."_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return $this->val($name);
    }

    public function __set($name, $value) {
        $method_name = __FUNCTION__."_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name($value);
        }
        $this->vars[$name] = $value;
    }

    public function __unset($name) {
        $method_name = __FUNCTION__."_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
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

}