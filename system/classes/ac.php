<?php

/**
 * Full Anidcore facade class
 * 
 * @method void on() on($name, $callable, $priority = 10)
 * @method mixed trigger() trigger($name, $eventArg = null)
 * @method void off() off($name)
 */
class Ac extends Ac_System {
    /**
     * @static 
     */
    
    /**
     * 
     */
    public static function get() {
        
    }

    public static function post() {
        
    }

    public static function input() {
        
    }

    public static function cookie() {
        
    }

    public static function __callStatic($name, $arguments) {
        $fns = array("on", "trigger", "off");
        foreach($fns as $fn){
            if (preg_match("/^{$fn}[A-Z]/", $name) || preg_match("/^{$fn}$/", $name)) {
                $event = lcfirst(preg_replace("/^({$fn})/", "", $name));
                if (!empty($event))
                    array_unshift($arguments, $event);
                return call_user_func_array(array(self::observer(), $fn), $arguments);
            }
        }
    }

}