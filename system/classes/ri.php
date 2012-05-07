<?php

define("RI_CHARS_HEXADECIMAL", "abcdef0123456789");
define("RI_CHARS_ALPHANUMERIC", "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
define("RI_CHARS_SYMBOLS", "{}()[]<>!?|@#%&/=^*;,:.-_+");

/**
 * Rino Framework main class
 */
class Ri {
    
    /**
     * Rino Framework Version 
     */
    const VERSION = "0.1.0";

    const MAIN_CONTEXT_NAME = "main";

    /**
     * Registry variables
     * @var array 
     */
    protected static $vars;

    /**
     * Registry variables that can only be assigned once
     * @var array 
     */
    protected static $finals;

    /**
     * Timers for profiling
     * @var array 
     */
    protected static $timers = array();

    /**
     * Gets / sets an application variable
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, sets the value of a var)
     * @return mixed 
     */
    public static function vars($varname = null) {
        if (empty($varname))
            return self::$vars;

        $args = func_get_args();
        if (count($args) == 2) {
            self::$vars[$varname] = $args[1];
            return null;
        } else {
            if (!isset(self::$vars[$varname]))
                return false;
            else
                return self::$vars[$varname];
        }
    }

    /**
     * Gets / sets an application variable that can only be assigned once
     * (final vars, pseudo constants)
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, assings a value to a non-existing var)
     * @return mixed 
     */
    public static function finals($varname = null) {
        if (empty($varname))
            return self::$finals;

        $args = func_get_args();
        if (count($args) == 2) {
            if (!isset(self::$finals[$varname]))
                self::$finals[$varname] = $args[1];
            return null;
        } else {
            if (!isset(self::$finals[$varname]))
                return false;
            else
                return self::$finals[$varname];
        }
    }

    public static function timerStart() {
        self::$timers[] = microtime(true);
    }

    public static function timerStop($start_time = null, $detailed_result = true) {
        if ($start_time == null) {
            if (!empty(self::$timers)) {
                $start_time = ri_arr_last(self::$timers);
                array_pop(self::$timers);
            }else
                return 0;
        }

        $end_time = round((microtime(true) - $start_time), 3);

        if ($detailed_result) {
            if ($end_time < 1) {
                $end_time_str = ($end_time * 1000) . "ms";
            }else
                $end_time_str = $end_time . "s";

            return $end_time_str;
        }else {
            return $end_time;
        }
    }

    public static function generateKeys($config) {
        //Security Keys
        $keys_file = RI_PATH_DATA . "keys.data";
        if (!is_readable($keys_file)) {
            $security_keys = array(
                "key.GLOBAL_SALT" => ri_str_random(64, RI_CHARS_ALPHANUMERIC
                        . RI_CHARS_SYMBOLS)
            );
            foreach ($config["key.names"] as $i => $kn) {
                $security_keys["key." . $kn] = ri_str_random(64, RI_CHARS_ALPHANUMERIC
                        . RI_CHARS_SYMBOLS);
            }
            file_put_contents($keys_file, serialize($security_keys));
        } else {
            $security_keys = unserialize(file_get_contents($keys_file));
        }

        foreach ($security_keys as $k => $val) {
            $config[$k] = $val;
        }
    }

    /**
     *
     * @param array $config
     * @throws RuntimeException 
     */
    public static function configureServer($config) {
        if (!is_dir(RI_PATH_LOGS))
            mkdir(RI_PATH_LOGS, 0770);
        if (!is_dir(RI_PATH_DATA))
            mkdir(RI_PATH_DATA, 0770);
        if (!is_dir(RI_PATH_CONTENT))
            mkdir(RI_PATH_CONTENT, 0775);

        //  Error reporting
        error_reporting($config["server.error_reporting"]);
        ini_set("display_errors", $config["server.display_errors"] ? true : false); //stdout = output, stderr=error log file
        //  PHP environment variables
        if (!ini_get('safe_mode')) {

            set_time_limit($config["server.max_execution_time"]);
            ini_set("memory_limit", $config["server.memory_limit"]);
            ini_set("max_execution_time", $config["server.max_execution_time"]);
            ini_set('upload_max_filesize', $config["server.upload_max_file_size"]);
            ini_set('post_max_size', $config["server.post_max_size"]);
            ini_set('max_input_time', $config["server.max_input_time"]);
            ini_set('default_charset', $config["server.default_charset"]);
            ini_set('default_mimetype', $config["server.default_mimetype"]);

            setlocale(LC_ALL, $config["server.locale"]);
            date_default_timezone_set($config["server.timezone"]);

            ini_set("log_errors", true);
            ini_set('error_log', $config["server.error_log_file"]);

            ini_set("session.name", $config["session.name"]);
            ini_set("session.use_cookies", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_path", $config["session.cookie_path"]);
            ini_set("session.cookie_secure", $config["session.cookie_secure"]);
            ini_set("session.cookie_lifetime", $config["session.cookie_lifetime"]);
            ini_set('session.gc_maxlifetime', $config["session.gc_maxlifetime"]);
            ini_set("session.use_trans_sid", 0); # do not use PHPSESSID in urls
            ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)
            session_cache_expire($config["session.cache_expire"]);
            session_cache_limiter($config["session.cache_limiter"]);
        } else {
            self::exception("Rino Framework cannot be executed under safe_mode");
        }
    }

    public static function classFind($class_name, array $modules) {
        $class_file = null;
        $class_name_dir = str_replace("_", _DS, strtolower($class_name));

        /* /modules/<module>/classes/ folder */
        foreach($modules as $name => $module){
            /*@var $module Ri_Module*/
            $class_file = $module->path . "classes" . _DS . $class_name_dir . ".php";
            $exists = self::classInclude($class_file, $class_name);
            if($exists){
                return $module;
            }
        }

        /* system/classes folder */
        $class_file = RI_PATH_SYSTEM . "classes" . _DS . $class_name_dir . ".php";
        if(self::classInclude($class_file, $class_name)){
            return true;
        }
        
        return false;
    }

    /**
     * Includes the class file (once) and verifies that the class is loaded
     * @param string $class_file Full class file path
     * @param string $class_name Class name to verify
     * @return boolean 
     */
    public static function classInclude($class_file, $class_name) {
        if (is_readable($class_file)) {
            include_once $class_file;
            return class_exists($class_name, false);
        }
        return false;
    }

}