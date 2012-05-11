<?php

class Ac_Model_Record extends Ac_Object {

    public function __isset($name) {
        return $this->__overload(__FUNCTION__, $name);
    }

    public function __get($name) {
        return $this->__overload(__FUNCTION__, $name);
    }

    public function __set($name, $value) {
        return $this->__overload(__FUNCTION__, $name, $value);
    }

    public function __unset($name) {
        return $this->__overload(__FUNCTION__, $name);
    }

    protected function __overload() {
        $args = func_get_args();
        $fn = array_shift($args);
        $name = array_shift($args);
        $method_name = $fn . "_$name";
        if (method_exists($this, $method_name)) {
            return call_user_func_array(array($this, $method_name), $args);
        } else {
            array_unshift($args, $name);
            return call_user_func_array("parent::$fn", $args);
        }
    }

}