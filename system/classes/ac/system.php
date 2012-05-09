<?php

/**
 * Base Anidcore facade class 
 */
class Ac_System {

    const VERSION = "0.5.0-WIP";

    ###
    ## ENVIRONMENT:

    /**
     *
     * @var Ac_Observer 
     */
    protected static $observer;

    /**
     * 
     * @var array 
     */
    protected static $config;

    /**
     *
     * @var Ac_Context 
     */
    protected static $context;

    /**
     *
     * @var Ac_Loader 
     */
    protected static $loader;

    /**
     * 
     * @var Ac_Http_Request 
     */
    protected static $request;

    /**
     *
     * @var Ac_Router 
     */
    protected static $router;

    /**
     *
     * @var Ac_Http_Response 
     */
    protected static $response;

    ###
    ## VARIABLE DATA:

    /**
     * 
     * @var stdClass 
     */
    protected static $reg;

    /**
     * 
     * @var array 
     */
    protected static $finals;

    /**
     *
     * @var Ac_Model_Globals_Session 
     */
    protected static $session;

    /**
     *
     * @var Ac_Model_Pdo 
     */
    protected static $db;

    /**
     *
     * @var Ac_Cache 
     */
    protected static $cache;

    /**
     *
     * @var Ac_Log 
     */
    protected static $log;

    private function __construct() {
        ;
    }

    private function __clone() {
        ;
    }

    public static function __init(array $config) {
        if (empty(self::$observer)) {
            self::$observer = new Ac_Observer();
            self::$config = Ac::trigger('AcBeforeInit', $config);
            self::$context = Ac_Context::getInstance();
            self::$loader = Ac_Loader::getInstance();
            Ac::trigger('AcLoader');
            self::$session = new Ac_Model_Globals_Session(self::config("session.name"),
                            self::config("session.sessid_lifetime"),
                            self::config("session.sessid_fingerprint_data"));
            self::$session->start();
            Ac::trigger('AcInit');
        }
    }

    public static function run($sendResponse = false) {
        if (empty(self::$observer))
            self::__init();
    }

    public static function config($name = null, $default = false, $module = null) {
        if (empty($module)) {
            if (empty($name))
                return self::$config;

            if (isset(self::$config[$name])) {
                return self::$config[$name];
            }
        } else {
            if (!isset(self::$config["modules.config"][$module]))
                return $default;

            if (empty($name))
                return self::$config["modules.config"][$module];

            if (isset(self::$config["modules.config"][$module][$name])) {
                return self::$config["modules.config"][$module][$name];
            }
        }

        return $default;
    }

    public static function setConfig($name, $value, $module = null) {
        if (empty($module)) {
            if (!empty($name))
                self::$config[$name] = $value;
            else
                self::$config = $value;
        }else {
            if (!empty($name))
                self::$config["modules.config"][$module][$name] = $value;
            else
                self::$config["modules.config"][$module] = $value;
        }
    }

    /**
     *
     * @return Ac_Context 
     */
    public static function context() {
        return self::$context;
    }

    /**
     *
     * @return Ac_Loader 
     */
    public static function loader() {
        return self::$loader;
    }

    /**
     *
     * @return Ac_Http_Request 
     */
    public static function request() {
        return self::$request;
    }

    /**
     *
     * @return Ac_Router
     */
    public static function router() {
        return self::$router;
    }

    /**
     *
     * @return Ac_Http_Response 
     */
    public static function response() {
        return self::$response;
    }

    /**
     *
     * @return stdClass 
     */
    public static function reg() {
        return self::$reg;
    }

    /**
     * Gets / sets a registry variable that can only be assigned once
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

    /**
     *
     * @return Ac_Model_Globals_Session 
     */
    public static function session() {
        return self::$session;
    }

    /**
     *
     * @return Ac_Storage_Pdo 
     */
    public static function db() {
        return self::$db;
    }

    /**
     *
     * @return Ac_Storage_Cache 
     */
    public static function cache() {
        return self::$cache;
    }

    /**
     * Returns the Ac_Log instance or calls the Ac_Log::log()
     * if some parameter is passed
     * @param mixed $data
     * @param string $label
     * @return Ac_Log|void
     */
    public static function log() {
        if (func_num_args() > 0) {
            return call_user_func_array(array(self::$log, 'log'), func_get_args());
        }
        return self::$log;
    }

    /**
     * Implements those magic functions<br>
     * for <b>Ac_Observer</b>
     *  - <i>void</i> <b>on</b>($name, $callable, $priority = 10)
     *  - <i>void</i> <b>on{EventName}</b>($callable, $priority = 10)
     *  - <i>mixed</i> <b>trigger</b>($name, $eventArg = null)
     *  - <i>mixed</i> <b>trigger{EventName}</b>($eventArg = null)
     *  - <i>void</i> <b>off</b>($name)
     *  - <i>void</i> <b>off{EventName}</b>()
     * 
     * @param type $name
     * @param type $arguments
     * @return type 
     */
    public static function __callStatic($name, $arguments) {
        $fns = array("trigger", "on", "off");
        foreach ($fns as $fn) {
            if (preg_match("/^{$fn}[A-Z]/", $name) || preg_match("/^{$fn}$/", $name)) {
                $event = strtolower(preg_replace("/^({$fn})/", "", $name));
                if (!empty($event))
                    array_unshift($arguments, $event);
                else
                    $arguments[0] = strtolower($arguments[0]);
                return call_user_func_array(array(self::$observer, $fn), $arguments);
            }
        }
        return null;
    }

    /**
     *
     * @param string $message
     * @param int $exitCode
     * @throws RuntimeException 
     */
    public static function exception($message, $exitCode = -1) {
        throw new RuntimeException("Anidcore Error: " . $message);
        exit($exitCode);
    }

}