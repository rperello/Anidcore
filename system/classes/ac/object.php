<?php

class Ac_Object implements ArrayAccess {

    /**
     * @var array 
     */
    protected $properties = array();

    public function __construct($properties = array()) {
        $this->properties = (array) $properties;
    }

    public function replace(array $properties) {
        $this->properties = $properties;
    }

    public function &getArray() {
        return $this->properties;
    }

    public function __isset($name) {
        return isset($this->properties[$name]);
    }

    public function &__get($name) {
        return $this->properties[$name];
    }

    public function __set($name, $value) {
        $this->properties[$name] = $value;
    }

    public function __unset($name) {
        if (isset($this->properties[$name])) {
            unset($this->properties[$name]);
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