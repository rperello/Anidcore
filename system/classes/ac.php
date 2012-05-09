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
        if (preg_match("/^on[A-Z]/", $name) || preg_match("/^on$/", $name)) {
            $event = lcfirst(preg_replace("/^(on)/", "", $name));
            if (!empty($event))
                array_unshift($arguments, $event);
            return call_user_func_array(array(self::observer(), 'on'), $arguments);
        }elseif (preg_match("/^trigger[A-Z]/", $name) || preg_match("/^trigger$/", $name)) {
            $event = lcfirst(preg_replace("/^(trigger)/", "", $name));
            if (!empty($event))
                array_unshift($arguments, $event);
            return call_user_func_array(array(self::observer(), 'trigger'), $arguments);
        }elseif (preg_match("/^off[A-Z]/", $name) || preg_match("/^off$/", $name)) {
            $event = lcfirst(preg_replace("/^(off)/", "", $name));
            if (!empty($event))
                array_unshift($arguments, $event);
            return call_user_func_array(array(self::observer(), 'off'), $arguments);
        }
    }

}