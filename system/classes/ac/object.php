<?php

class Ac_Object implements ArrayAccess {

    /**
     * @var array 
     */
    protected $properties = array();

    public function __construct(array $properties = array()) {
        $this->properties = $properties;
    }

    public function properties($properties = null) {
        if (is_array($properties))
            $this->properties = $properties;
        return $this->properties;
    }

    public function __isset($name) {
        return isset($this->properties[$name]);
    }

    public function __get($name) {
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
     * Returns a new instance of the called class
     * @return Ac_Object 
     */
    public static function factory() {
        return new static(func_get_args());
    }

    /**
     * Returns the constant value of the called class
     * @param string $name constant name
     * @return mixed 
     */
    public static function constant($name) {
        return defined("static::$name") ? constant("static::$name") : null;
    }

}