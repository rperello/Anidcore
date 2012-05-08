<?php

class Ac_Object extends Ac_Array {

    public function __isset($name) {
        $method_name = __FUNCTION__ . "_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return parent::__isset($name);
    }

    public function __get($name) {
        $method_name = __FUNCTION__ . "_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return parent::__get($name);
    }

    public function __set($name, $value) {
        $method_name = __FUNCTION__ . "_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name($value);
        }
        return parent::__set($name, $value);
    }

    public function __unset($name) {
        $method_name = __FUNCTION__ . "_$name";
        if (method_exists($this, $method_name)) {
            return $this->$method_name();
        }
        return parent::__unset($name);
    }

}