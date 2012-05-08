<?php

spl_autoload_register(array('Ac', 'autoload'));

class Ac {

    const VERSION = "0.4.5";
    /**
     *  1. Triggered before initializing Anidcore (through Ac::init).
     * 
     *  <b>Parameters</b>: array <i>$config</i> The merged config<br>
     *  <b>Replaces</b>: The return value will replace the site config
     */
    const HOOK_BEFORE_INIT = 'ac.before_init';

    /**
     *  2. Triggered everytime a module is created.
     * 
     *  <b>Parameters</b>: Ac_Module <i>$module</i> The module instance
     */
    const HOOK_ON_LOAD_MODULE = 'ac.on_load_module';

    /**
     *  3. Triggered when the 'app' module is created.
     * 
     *     Each module will trigger its own 'ac.on_load_module_<modulename>' hook.
     * 
     *  <b>Parameters</b>: Ac_Module <i>$module</i> The module instance
     */
    const HOOK_ON_LOAD_MODULE_APP = 'ac.on_load_module_app';


    /**
     *  4. Triggered everytime a module is initialized
     *     (when its functions.php and init.php files are included).
     * 
     *  <b>Parameters</b>: Ac_Module <i>$module</i> The module instance
     */
    const HOOK_ON_INIT_MODULE = 'ac.on_init_module';


    /**
     *  5. Triggered before the router resolves the controller and the action.
     * 
     *  <b>Parameters</b>: <i>Ac_Router</i> <b>$router</b> The router instance
     */
    const HOOK_BEFORE_ROUTER_RESOLVE = 'ac.before_router_resolve';

    /**
     *  6. Triggered when the 'app' module is initialized
     *     (when its functions.php and init.php files are included).
     * 
     *     Each module will trigger its own 'ac.on_init_module_<modulename>' hook.
     * 
     *  <b>Parameters</b>: Ac_Module <i>$module</i> The module instance
     */
    const HOOK_ON_INIT_MODULE_APP = 'ac.on_init_module_app';


    /**
     *  7. Triggered only once, at the beginning of Ac::init.
     * 
     *  <b>Parameters</b>: string <i>$resource</i> The requested resource<br>
     *  <b>Replaces</b>: The return value will replace the requested resource
     *  the router will use to resolve controller and action.
     */
    const HOOK_ON_ROUTER_RESOURCE = 'ac.before_router_resource';


    /**
     *  8. Triggered when the router resolves the controller and the action.
     * 
     *  <b>Parameters</b>: <i>Ac_Router</i> <b>$router</b> The router instance
     */
    const HOOK_ON_ROUTER_RESOLVE = 'ac.on_router_resolve';


    /**
     *  9. Triggered when Anidcore has been initializated (through Ac::init).
     */
    const HOOK_ON_INIT = 'ac.on_init';


    /**
     *  10. Triggered before the action is called and/or the response sent
     */
    const HOOK_BEFORE_RUN = 'ac.before_run';


    /**
     *  11. Triggered before the controller is created and action called
     * 
     *  <b>Parameters</b>: <i>Ac_Router</i> <b>$router</b> The router instance
     */
    const HOOK_BEFORE_ROUTER_CALL = 'ac.before_router_call';


    /**
     *  12. Triggered when the controller is created and action called.
     * 
     *  <b>Parameters</b>: <i>mixed</i> <b>$result</b> The result returned by the action call
     *  <b>Replaces</b>: The return value will replace the action result
     */
    const HOOK_ON_ROUTER_CALL = 'ac.on_router_call';


    /**
     *  13. Triggered before the response is printed and before response headers are sent.
     *      The output buffer is cleaned here using ob_get_clean()
     * 
     *  <b>Parameters</b>: <i>array</i> array containing <i>'responseBody'</i> and <i>'outputBuffer'</i> data<br>
     *  <b>Replaces</b>: The return value must be an array with the same keys, and it will replace
     *  the main response body returned in $return['responseBody']. The 'outputBuffer' will be passed
     *  to HOOK_ON_SEND_RESPONSE too.
     */
    const HOOK_BEFORE_SEND_RESPONSE = 'ac.before_send_response';


    /**
     *  14. Triggered when the response is printed and response headers are sent.
     * 
     *  <b>Parameters</b>: <i>string</i> the output buffer before the response was sent.<br>
     */
    const HOOK_ON_SEND_RESPONSE = 'ac.on_send_response';


    /**
     *  15. Triggered when the run function terminates (on router call and response sent)
     */
    const HOOK_ON_RUN = 'ac.on_run';

    /**
     * Ac environment variables
     * @var array 
     */
    protected static $env = null;

    /**
     * Anidcore Registry
     * @var Ac_Array
     */
    public static $reg;

    public static function init() {
        ob_start(); //start capturing buffer to prevent undesired echo
        if (self::$env === null) {
            self::$env = array();
            self::$reg = array();
            self::$env["config"] = self::hookApply(self::HOOK_BEFORE_INIT, array_merge(self::defaults(), include AC_PATH_APP . "config.php"));
            self::configureServer();
            self::generateKeys();

            self::request();
            self::response();
            self::dbc();

            self::module("app");

            self::router();


            //load all modules defined in modules.autoload
            foreach (self::config("modules.autoload") as $moduleName) {
                if ($moduleName != "app") {
                    self::module($moduleName);
                }
            }

            //initialize all modules defined in modules.autoload
            foreach (self::$env["loaded_modules"] as $moduleName => $mod) {
                if ($moduleName != "app") {
                    $mod->init();
                }
            }

            //initialize main module
            self::module("app")->init();

            //resolve request resource
            self::router()->resolve();

            self::hookApply(self::HOOK_ON_INIT);
        }
    }

    public static function environment(array $env = null) {
        if (!empty($env)) {
            self::$env = $env;
        }
        return self::$env;
    }

    /**
     * Calls the router and returns the action return value
     */
    public static function run($sendResponse = false) {
        self::hookApply(self::HOOK_BEFORE_RUN);
        $action_result = self::router()->call();

        if ($sendResponse) {
            // get and clean output buffer previously started to capture in Ac::init
            $ob = ob_get_clean();
            $data = self::hookApply(self::HOOK_BEFORE_SEND_RESPONSE, array("responseBody" => self::response()->body(), "outputBuffer" => $ob));
            self::response()->body($data["responseBody"]);
            self::response()->send();
            self::hookApply(self::HOOK_ON_SEND_RESPONSE, $data['outputBuffer']);
        }

        self::hookApply(self::HOOK_ON_RUN);
        return $action_result;
    }

    /**
     *
     * @return Ac_Http_Request 
     */
    public static function request() {
        if (empty(self::$env["request"])) {
            self::$env["request"] = new Ac_Http_Request();
        }
        return self::$env["request"];
    }

    /**
     *
     * @return Ac_Router 
     */
    public static function router() {
        if (empty(self::$env["router"])) {
            self::$env["router"] = new Ac_Router();
        }
        return self::$env["router"];
    }

    /**
     *
     * @return Ac_Http_Response 
     */
    public static function response() {
        if (empty(self::$env["response"])) {
            self::$env["response"] = new Ac_Http_Response();
        }
        return self::$env["response"];
    }

    /**
     *
     * @return Ac_Global_Server 
     */
    public static function server() {
        if (empty(self::$env["_server"])) {
            self::$env["_server"] = new Ac_Global_Server();
        }
        return self::$env["_server"];
    }

    /**
     *
     * @return Ac_Global_Get
     */
    public static function get() {
        if (empty(self::$env["_get"])) {
            self::$env["_get"] = new Ac_Global_Get();
        }
        return self::$env["_get"];
    }

    /**
     *
     * @return Ac_Global_Post 
     */
    public static function post() {
        if (empty(self::$env["_post"])) {
            self::$env["_post"] = new Ac_Global_Post();
        }
        return self::$env["_post"];
    }

    /**
     *
     * @return Ac_Global_Cookie 
     */
    public static function cookie() {
        if (empty(self::$env["_cookie"])) {
            self::$env["_cookie"] = new Ac_Global_Cookie();
        }
        return self::$env["_cookie"];
    }

    /**
     *
     * @return Ac_Global_Session 
     */
    public static function session() {
        if (empty(self::$env["_session"])) {
            self::$env["_session"] = new Ac_Global_Session(self::config("session.name"),
                            self::config("session.sessid_lifetime"), self::request()->clientIP);
        }
        return self::$env["_session"];
    }

    /**
     *
     * @return Ac_Global_Env 
     */
    public static function env() {
        if (empty(self::$env["_env"])) {
            self::$env["_env"] = new Ac_Global_Env();
        }
        return self::$env["_env"];
    }

    /**
     * 
     * @return Ac_Dbc Returns a database connection and initializes them if needed
     */
    public static function dbc($instanceName = null) {
        if (Ac_Dbc::hasConnections()) {
            return Ac_Dbc::getConnection($instanceName);
        } else {
            Ac_Dbc::init(self::config("database"));
            return Ac_Dbc::getConnection($instanceName);
        }
    }

    /**
     *
     * @return Ac_Logger
     * @throws RuntimeException
     */
    public static function logger() {
        if (empty(self::$env["logger"])) {
            $logger_class = self::config("logger.class", "Ac_Logger");
            self::$env["logger"] = new $logger_class();
            if (!(self::$env["logger"] instanceof Ac_Logger)) {
                self::exception("The logger.class must be Ac_Logger or extended from it");
            }
        }
        return self::$env["logger"];
    }

    /**
     *
     * @return Ac_Cache
     * @throws RuntimeException
     */
    public static function cache() {
        if (empty(self::$env["cache"])) {
            $cache_class = self::config("cache.class", "Ac_Cache_File");
            self::$env["cache"] = new $cache_class();
            if (!(self::$env["cache"] instanceof Ac_Cache)) {
                self::exception("The cache.class must be Ac_Cache or extended from it");
            }
        }
        return self::$env["cache"];
    }

    public static function config($name = null, $default = false, $module = null) {
        if (empty($module)) {
            if (empty($name))
                return self::$env["config"];

            if (isset(self::$env["config"][$name])) {
                return self::$env["config"][$name];
            }
        } else {
            if (!isset(self::$env["config"]["modules.config"][$module]))
                return $default;

            if (empty($name))
                return self::$env["config"]["modules.config"][$module];

            if (isset(self::$env["config"]["modules.config"][$module][$name])) {
                return self::$env["config"]["modules.config"][$module][$name];
            }
        }

        return $default;
    }

    public static function setConfig($name, $value, $module = null) {
        if (empty($module)) {
            if (!empty($name))
                self::$env["config"][$name] = $value;
            else
                self::$env["config"] = $value;
        }else {
            if (!empty($name))
                self::$env["config"]["modules.config"][$module][$name] = $value;
            else
                self::$env["config"]["modules.config"][$module] = $value;
        }
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
            return self::$env["finals"];

        $args = func_get_args();
        if (count($args) == 2) {
            if (!isset(self::$env["finals"][$varname]))
                self::$env["finals"][$varname] = $args[1];
            return null;
        } else {
            if (!isset(self::$env["finals"][$varname]))
                return false;
            else
                return self::$env["finals"][$varname];
        }
    }

    ## MODULES

    /**
     *
     * @param string $moduleName
     * @return Ac_Module 
     */
    public static function module($moduleName = null) {
        if (empty($moduleName))
            return self::moduleGetCurrent();
        if (!isset(self::$env["loaded_modules"][$moduleName])) {
            self::$env["loaded_modules"][$moduleName] = new Ac_Module($moduleName);
        }
        return self::$env["loaded_modules"][$moduleName];
    }

    public static function moduleUnload($moduleName) {
        if ($moduleName == "app")
            return false;

        if (self::moduleIsLoaded($moduleName)) {
            unset(self::$env["loaded_modules"][$moduleName]);
            return true;
        }

        if ($moduleName == self::$env["current_module_name"])
            self::$env["current_module_name"] = "app";
        return false;
    }

    public static function moduleIsLoaded($moduleName) {
        return isset(self::$env["loaded_modules"][$moduleName]);
    }

    public static function modulesLoaded() {
        return self::$env["loaded_modules"];
    }

    /**
     *
     * @return Ac_Module 
     */
    public static function moduleGetCurrent() {
        if (count(self::$env["loaded_modules"]) > 0) {
            if (self::moduleIsLoaded(self::$env["current_module_name"])) {
                return self::$env["loaded_modules"][self::$env["current_module_name"]];
            }
        }
        return false;
    }

    public static function moduleSetCurrent($moduleName) {
        if (!self::moduleIsLoaded($moduleName)) {
            self::module($moduleName);
        }
        self::$env["current_module_name"] = $moduleName;
    }

    /*     * *** HOOKS **** */

    /**
     * Assign hook
     * @param   string  $name       The hook name
     * @param   mixed   $callable   A callable object
     * @param   int     $priority   The hook priority; 0 = high, 10 = low
     * @return  void
     */
    public static function hookRegister($name, $callable, $priority = 10) {
        if (!isset(self::$env["hooks"][$name])) {
            self::$env["hooks"][$name] = array(array());
        }
        if (is_callable($callable)) {
            self::$env["hooks"][$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke hook
     * @param   string  $name       The hook name
     * @param   mixed   $hookArg   (Optional) Argument for hooked functions
     * @return  mixed
     */
    public static function hookApply($name, $hookArg = null) {
        if (!isset(self::$env["hooks"][$name])) {
            self::$env["hooks"][$name] = array(array());
        }
        if (!empty(self::$env["hooks"][$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count(self::$env["hooks"][$name]) > 1) {
                ksort(self::$env["hooks"][$name]);
            }
            foreach (self::$env["hooks"][$name] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        //hook functions should return the (modified?) $hookArg
                        $hookArg = call_user_func_array($callable, $hookArg);
                    }
                }
            }
            return $hookArg;
        }
    }

    /**
     * Get hook listeners
     *
     * Return an array of registered hooks. If `$name` is a valid
     * hook name, only the listeners attached to that hook are returned.
     * Else, all listeners are returned as an associative array whose
     * keys are hook names and whose values are arrays of listeners.
     *
     * @param   string      $name A hook name (Optional)
     * @return  array|null
     */
    public static function hookGet($name = null) {
        if (!empty($name)) {
            return isset(self::$env["hooks"][(string) $name]) ? self::$env["hooks"][(string) $name] : null;
        } else {
            return self::$env["hooks"];
        }
    }

    /**
     * Clear hook listeners
     *
     * Clear all listeners for all hooks. If `$name` is
     * a valid hook name, only the listeners attached
     * to that hook will be cleared.
     *
     * @param   string  $name   A hook name (Optional)
     * @return  void
     */
    public static function hookClear($name = null) {
        if (!empty($name) && isset(self::$env["hooks"][(string) $name])) {
            self::$env["hooks"][(string) $name] = array(array());
        } else {
            foreach (self::$env["hooks"] as $key => $value) {
                self::$env["hooks"][$key] = array(array());
            }
        }
    }

    public static function timerStart() {
        self::$env["timers"][] = microtime(true);
    }

    public static function timerStop($start_time = null, $detailed_result = true) {
        if ($start_time == null) {
            if (!empty(self::$env["timers"])) {
                $start_time = end(array_values(self::$env["timers"]));
                array_pop(self::$env["timers"]);
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

    protected static function generateKeys() {
        //Security Keys
        $keys_file = AC_PATH_DATA . "keys.data";
        if (!is_readable($keys_file)) {
            $security_keys = array(
                "key.GLOBAL_SALT" => ac_str_random(64, AC_CHARS_ALPHANUMERIC
                        . AC_CHARS_SYMBOLS)
            );
            foreach (self::$env["config"]["key.names"] as $i => $kn) {
                $security_keys["key." . $kn] = ac_str_random(64, AC_CHARS_ALPHANUMERIC
                        . AC_CHARS_SYMBOLS);
            }
            file_put_contents($keys_file, serialize($security_keys));
        } else {
            $security_keys = unserialize(file_get_contents($keys_file));
        }

        foreach ($security_keys as $k => $val) {
            self::$env["config"][$k] = $val;
        }
    }

    /**
     *
     * @param array $config
     * @throws RuntimeException 
     */
    protected static function configureServer() {
        if (!is_dir(AC_PATH_LOGS))
            mkdir(AC_PATH_LOGS, 0770);
        if (!is_dir(AC_PATH_DATA))
            mkdir(AC_PATH_DATA, 0770);
        if (!is_dir(AC_PATH_CONTENT))
            mkdir(AC_PATH_CONTENT, 0775);

        //  Error reporting
        error_reporting(self::$env["config"]["server.error_reporting"]);
        ini_set("display_errors", self::$env["config"]["server.display_errors"] ? true : false); //stdout = output, stderr=error log file
        //  PHP environment variables
        if (!ini_get('safe_mode')) {

            set_time_limit(self::$env["config"]["server.max_execution_time"]);
            ini_set("memory_limit", self::$env["config"]["server.memory_limit"]);
            ini_set("max_execution_time", self::$env["config"]["server.max_execution_time"]);
            ini_set('upload_max_filesize', self::$env["config"]["server.upload_max_file_size"]);
            ini_set('post_max_size', self::$env["config"]["server.post_max_size"]);
            ini_set('max_input_time', self::$env["config"]["server.max_input_time"]);
            ini_set('default_charset', self::$env["config"]["server.default_charset"]);
            ini_set('default_mimetype', self::$env["config"]["server.default_mimetype"]);

            setlocale(LC_ALL, self::$env["config"]["server.locale"]);
            date_default_timezone_set(self::$env["config"]["server.timezone"]);

            ini_set("log_errors", true);
            ini_set('error_log', self::$env["config"]["server.error_log_file"]);

            ini_set("session.name", self::$env["config"]["session.name"]);
            ini_set("session.use_cookies", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_path", self::$env["config"]["session.cookie_path"]);
            ini_set("session.cookie_secure", self::$env["config"]["session.cookie_secure"]);
            ini_set("session.cookie_lifetime", self::$env["config"]["session.cookie_lifetime"]);
            ini_set('session.gc_maxlifetime', self::$env["config"]["session.gc_maxlifetime"]);
            ini_set("session.use_trans_sid", 0); # do not use PHPSESSID in urls
            ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)
            session_cache_expire(self::$env["config"]["session.cache_expire"]);
            session_cache_limiter(self::$env["config"]["session.cache_limiter"]);
        } else {
            self::exception("Anidcore Framework cannot be executed under safe_mode");
        }
    }

    public static function defaults() {
        if (empty(self::$env["defaults"])) {
            self::$env["defaults"] = array(
                "server.default_mimetype" => "text/html",
                "server.default_charset" => "UTF-8",
                "server.locale" => "en_US.UTF8",
                "server.timezone" => "UTC",
                "server.memory_limit" => "180M",
                "server.max_execution_time" => 60,
                "server.max_input_time" => -1,
                "server.post_max_size" => "24M",
                "server.upload_max_file_size" => "16M",
                "server.display_errors" => true,
                "server.error_reporting" => -1, //E_ALL & ~E_STRICT; // -1 | E_STRICT
                "server.error_log_file" => AC_PATH_LOGS . 'php_errors.log',
                "session.name" => "phpsessid_" . strtolower(str_replace("/", "", AC_BASEDIR)),
                "session.sessid_lifetime" => 180,
                "session.cookie_path" => '/' . AC_BASEDIR,
                "session.cookie_secure" => (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")),
                "session.cookie_lifetime" => 0,
                "session.gc_maxlifetime" => 1440,
                "session.cache_expire" => 180,
                "session.cache_limiter" => "nocache",
                "logger.enabled" => true,
                "logger.class" => "Ac_Logger",
                "cache.enabled" => false,
                "cache.class" => "Ac_Cache_File",
                "key.names" => array(), //extra generated keys
                "modules.config" => array(),
                "modules.autoload" => array(),
                "router.default_controller" => "index",
                //Request extensions that will cause a '404 Not Found' header
                "media_extensions" => array(
                    //scripts and styles
                    "js", "j", "css", "less",
                    //images
                    "gif", "jpg", "jpeg", "png", "webp", "svg", "svgz",
                    //fonts
                    "woff", "ttf", "eot", "otf",
                    //audio
                    "mp3", "oga", "ogg", "wma", "wav",
                    //video
                    "mp4", "webm", "mov", "mkv", "mpg", "ogv", "avi", "wmv",
                    //flash
                    "flv", "swf",
                ),
                "cache.path" => AC_PATH_APP . "cache" . _DS
            );
        }
        return self::$env["defaults"];
    }

    public static function classFind($class_name) {
        $class_file = null;
        $class_name_dir = str_replace("_", _DS, strtolower($class_name));

        /* /modules/<module>/classes/ folder */
        foreach (self::$env["loaded_modules"] as $name => $module) {
            /* @var $module Ac_Module */
            $class_file = $module->path . "classes" . _DS . $class_name_dir . ".php";
            $exists = self::classInclude($class_file, $class_name);
            if ($exists) {
                return $module;
            }
        }

        /* system/classes folder */
        $class_file = AC_PATH_SYSTEM . "classes" . _DS . $class_name_dir . ".php";
        if (self::classInclude($class_file, $class_name)) {
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

    public static function autoload($class_name) {
        $result = self::classFind($class_name);
        // class loaded from /system
        if ($result === true) {
            return true;
        } elseif ($result instanceof Ac_Module) {
            // class loaded from /app or /modules/*
            self::$env["current_module_name"] = $result->name;
            return true;
        }

        // class not found
        return false;
    }

    public static function exception($message, $exitCode = -1) {
        throw new RuntimeException("Anidcore FATAL ERROR: " . $message);
        exit($exitCode);
    }

}