<?php

class Ac_Object implements ArrayAccess, IteratorAggregate, Countable{

    /**
     * @var array 
     */
    protected $vars = array();

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

    /**
     * IteratorAggregate
     *
     * @return ArrayIterator
     */
    public function getIterator() {
        return new ArrayIterator($this->vars);
    }
    
    public function count() {
        return count($this->vars);
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