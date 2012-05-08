<?php

spl_autoload_register(array('Ac', 'autoload'));

class Ac {

    const VERSION = "0.4.4";

    /**
     * Ac environment variables
     * @var array 
     */
    protected static $env = array(
        "defaults" => array(),
        "config" => null,
        "vars" => array(),
        "finals" => array(),
        "hooks" => array(),
        "timers" => array(),
        "loaded_modules" => array(),
        "current_module_name" => null,
        "request" => null,
        "router" => null,
        "response" => null,
        "storage" => null,
        "logger" => null,
    );

    public static function init() {
        if (self::$env["config"] === null) {
            self::$env["config"] = self::hookApply("ac.before.init", array_merge(self::defaults(), include AC_PATH_APP . "config.php"));
            self::configureServer();
            self::generateKeys();

            self::request();
            self::response();
            self::storage();

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

            self::hookApply("ac.on.init");
        }
    }

    public static function environment($newEnv = null) {
        if (!empty($newEnv)) {
            self::$env = $newEnv;
        }
        return self::$env;
    }

    /**
     * Calls the router and returns the action return value
     */
    public static function run($sendResponse = false) {
        self::hookApply('ac.before.run');
        $action_result = self::router()->call();

        if ($sendResponse) {
            $before_render = self::hookApply('ac.before.response', array("body" => self::response()->body(), "ob" => ob_get_clean()));
            self::response()->body($before_render["body"]);
            self::response()->send();
            self::hookApply('ac.on.response', $before_render['ob']);
        }

        self::hookApply('ac.on.run');
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
     * @return Ac_Storage 
     */
    public static function storage() {
        if (empty(self::$env["storage"])) {
            self::$env["storage"] = new Ac_Storage();
        }
        return self::$env["storage"];
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
     * Gets / sets a registry variable
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, sets the value of a var)
     * @return mixed 
     */
    public static function vars($varname = null) {
        if (empty($varname))
            return self::$env["vars"];

        $args = func_get_args();
        if (count($args) == 2) {
            self::$env["vars"][$varname] = $args[1];
            return null;
        } else {
            if (!isset(self::$env["vars"][$varname]))
                return false;
            else
                return self::$env["vars"][$varname];
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
                "key.names" => array("AUTH_SALT"), //extra generated keys
                "modules.config" => array(),
                "modules.autoload" => array(),
                "router.default_controller" => "index",
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