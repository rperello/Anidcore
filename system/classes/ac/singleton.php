<?php

class Ac_Singleton {

    private static $_instances = array();

    protected function __construct() {
        
    }

    private final function __clone() {
        
    }

    /**
     *
     * @return Ac_Singleton 
     */
    public static function getInstance() {
        $class_name = get_called_class();
        if (!isset(self::$_instances[$class_name])) {
            self::$_instances[$class_name] = new $class_name(func_get_args());
        }
        return self::$_instances[$class_name];
    }

}