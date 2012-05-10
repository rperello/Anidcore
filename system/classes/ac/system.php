<?php

/**
 * Base Anidcore facade class 
 */
class Ac_System {

    const VERSION = "4.0.0-WIP";

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
    ## DATA MANIPULATION:

    /**
     * 
     * @var stdClass 
     */
    protected static $reg;

    /**
     * Finals are variables that only can be assigned once
     * Anidcore provides a way to emulate them using this variable
     * and the finals() function
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
     * @var Ac_Storage_Cache 
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

    public static function __init() {
        if (empty(self::$observer)) {
            self::$reg = new stdClass();
            self::$finals = array();
            self::$observer = new Ac_Observer();
            self::$loader = Ac_Loader::getInstance();
            self::$config = Ac::trigger('AcBeforeInit', self::$loader->getConfig());
            if (empty(self::$context))
                self::$context = Ac_Context::getInstance();
            Ac::trigger('AcLoaderLoad');
            self::$session = new Ac_Model_Globals_Session(self::config("session.name"),
                            self::config("session.sessid_lifetime"),
                            self::config("session.sessid_fingerprint_data"));
            self::$session->start();
            if (empty(self::$request))
                self::$request = new Ac_Http_Request(self::$context);
            if (empty(self::$response))
                self::$response = new Ac_Http_Response();

            //Initialize db connections (and connect if autoconnect==true in their config)
            //self::db();

            self::loader()->setActiveModule("app", true);
            if (empty(self::$router))
                self::$router = new Ac_Router();

            //load and initialize all modules defined in modules.autoload
            foreach (self::config("modules.autoload") as $moduleName) {
                if ($moduleName != "app") {
                    $mod = self::module($moduleName);
                    $mod->init();
                    Ac::trigger('AcInitModule', $mod);
                    Ac::trigger('AcInitModule_' . $moduleName, $mod);
                }
            }
            // init app module after all other modules
            self::module("app")->init();
            Ac::trigger('AcInitModule', self::module("app"));
            Ac::trigger('AcInitModule_app', self::module("app"));

            //resolve request resource
            self::$router->resolve();

            Ac::trigger('AcInit');
        }
    }

    /**
     * Calls the router and returns the action return value
     * @param boolean $sendResponse
     * @return mixed The action call return value 
     */
    public static function run($sendResponse = false) {
        if (empty(self::$observer))
            self::__init();
        ob_start();
        self::trigger("AcBeforeRun");
        $action_result = self::router()->call();

        if ($sendResponse) {
            $body = self::trigger('AcBeforeSendResponse', self::response()->body());
            self::response()->body($body);
            $ob = self::response()->send(true, true);
            self::trigger('AcSendResponse', $ob);
        }
        if (!isset($ob)) {
            if (ob_get_level())
                $ob = ob_get_clean();
            else
                $ob = null;
        }

        self::trigger("AcRun", $ob);
        return $action_result;
    }

    /**
     *
     * @param string $name
     * @param boolean $autoimport
     * @return Ac_Module 
     */
    public static function module($name = null, $autoimport = true) {
        return self::loader()->loadModule($name, $autoimport);
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
     * Get / Set the context
     * @param Ac_Context $context The new context
     * @return Ac_Context 
     */
    public static function &context(Ac_Context &$context = null) {
        if (!empty($context))
            self::$context = $context;
        return self::$context;
    }

    /**
     * Get / Set the router
     * @param Ac_Loader $loader The new loader
     * @return Ac_Loader 
     */
    public static function &loader(Ac_Loader &$loader = null) {
        if (!empty($loader))
            self::$loader = $loader;
        return self::$loader;
    }

    /**
     * Get / Set the request
     * @param Ac_Http_Request $request The new request
     * @return Ac_Http_Request 
     */
    public static function &request(Ac_Http_Request &$request = null) {
        if (!empty($request))
            self::$request = $request;
        return self::$request;
    }

    /**
     * Get / Set the router
     * @param Ac_Router $router The new router
     * @return Ac_Router
     */
    public static function &router(Ac_Router &$router = null) {
        if (!empty($router))
            self::$router = $router;
        return self::$router;
    }

    /**
     * Get / Set the response
     * @param Ac_Http_Response $response The new response
     * @return Ac_Http_Response 
     */
    public static function &response(Ac_Http_Response &$response = null) {
        if (!empty($response))
            self::$response = $response;
        return self::$response;
    }

    ################

    /**
     *
     * @return stdClass 
     */
    public static function &reg() {
        return self::$reg;
    }

    /**
     * Gets / sets a registry variable that can only be assigned once
     * (final vars, pseudo constants)
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, assings a value to a non-existing var)
     * @return mixed 
     */
    public static function &finals($varname = null) {
        if (empty($varname))
            return self::$finals;

        $varname = strtolower($varname);
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
    public static function &session() {
        return self::$session;
    }

    /**
     * 
     * @return Ac_Storage_Pdo|false Returns a database connection and initializes them if needed
     */
    public static function db($instanceName = null) {
        if (Ac_Storage_Pdo::hasConnections()) {
            return Ac_Storage_Pdo::getConnection($instanceName);
        } elseif (self::config("database")) {
            Ac_Storage_Pdo::init(self::config("database"));
            return Ac_Storage_Pdo::getConnection($instanceName);
        }
        return false;
    }

    /**
     *
     * @return Ac_Storage_Cache 
     */
    public static function &cache() {
        if (empty(self::$cache)) {
            $cache_class = self::config("cache.class", "Ac_Storage_Cache_File");
            self::$cache = new $cache_class(self::config("cache.path"));
            if (!(self::$cache instanceof Ac_Storage_Cache)) {
                self::exception("$cache_class is not a valid Ac_Storage_Cache instance");
            }
        }
        return self::$cache;
    }

    /**
     * Returns the Ac_Log instance or calls the Ac_Log::log() function if some parameter is passed
     * @param mixed $data
     * @param string $label
     * @param array $options
     * @return Ac_Log|void
     */
    public static function &log() {
        if (empty(self::$log)) {
            $log_class = self::config("log.class", "Ac_Log_File");
            self::$log = new $log_class();
            if (!(self::$log instanceof Ac_Log)) {
                self::exception("$log_class is not a valid Ac_Log instance");
            }
        }
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
     * You can also retrieve final or registry variables using magic method Ac::<finalOrRegistryName>().
     * So if you declare a final named 'flash' you can retrieve it using Ac::flash()
     * 
     * @param type $name
     * @param type $arguments
     * @return type 
     */
    public static function __callStatic($name, $arguments) {
        // Observers:
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

        // Finals:
        $lcname = strtolower($name);
        if (isset(self::$finals[$lcname])) {
            if (is_callable(self::$finals[$lcname])) {
                return call_user_func_array(self::$finals[$lcname], $arguments);
            } else {
                return self::$finals[$lcname];
            }
        }

        // Registry:
        if (isset(self::$reg->$name)) {
            if (is_callable(self::$reg->$name)) {
                return call_user_func_array(self::$reg->$name, $arguments);
            } else {
                return self::$reg->$name;
            }
        }

        self::exception("Call to undefined method Ac_System::$name()");

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