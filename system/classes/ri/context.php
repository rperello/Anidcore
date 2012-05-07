<?php

class Ri_Context {

    /**
     * @var array[Ri_Context]
     */
    protected static $instances = array();
    protected static $defaults;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Ri_Storage
     */
    public $storage;

    /**
     * @var Ri_Http_Request
     */
    public $request;

    /**
     * @var Ri_Http_Response
     */
    public $response;

    /**
     * @var Ri_Router
     */
    public $router;

    /**
     * @var Ri_Log
     */
    protected $log;

    /**
     * @var array key-value pair array of context settings
     */
    protected $config;

    /**
     *
     * @var array 
     */
    protected $loaded_modules = array();

    /**
     *
     * @var string 
     */
    protected $current_module_name;

    /**
     *  <b>Predefined hooks:</b><br>
     *  ri.before.create,
      ri.on.load_module,
      ri.on.load_module.<modulename>,
      ri.on.load_module,
      ri.on.load_module.<modulename>
      ri.before.router_resolve,
      ri.on.router_resource,
      ri.on.router_resolve,
      ri.on.init_module,
      ri.on.init_module.<modulename>,
      ri.on.init_module,
      ri.on.init_module.<modulename>,
      ri.on.create,
      ri.before.execute,
      ri.before.router_call,
      ri.on.router_call,
      ri.on.execute
     * @var array Event hooks
     */
    protected $hooks = array();

    public function __construct($config = array(), Ri_Http_Request $request = null, $name = null) {
        $hook = $this->hookApply("ri.before.create", array("config" => $config, "request" => $request));
        extract($hook);

        spl_autoload_register(array($this, 'autoload'));

        if (empty(self::$instances)) {
            $name = Ri::MAIN_CONTEXT_NAME;
        } elseif (empty($name)) {
            $name = "c_" . ri_str_random(12);
        }

        $this->name = $name;
        $this->current_module_name = $name;

        if (isset(self::$instances[$this->name])) {
            Ri::throwException("Context '" . $this->name . "' already exists.");
        }

        $this->request = empty($request) ? Ri_Http_Request::getInstance() : $request;
        $this->response = new Ri_Http_Response();

        if (empty(self::$defaults)) {
            self::$defaults = array(
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
                "server.error_log_file" => RI_PATH_LOGS . 'php_errors.log',
                "session.name" => "phpsessid_" . strtolower(str_replace("/", "", $this->request->baseDir)),
                "session.sessid_lifetime" => 180,
                "session.cookie_path" => '/' . $this->request->baseDir,
                "session.cookie_secure" => $this->request->isHttps(),
                "session.cookie_lifetime" => 0,
                "session.gc_maxlifetime" => 1440,
                "session.cache_expire" => 180,
                "session.cache_limiter" => "nocache",
                "log.enabled" => true,
                "log.class" => "Ri_Log",
                "key.names" => array("AUTH_SALT"), //extra generated keys
                "modules.config" => array(),
                "modules.autoload" => array(),
                "router.default_controller" => "index",
            );
        }

        $this->config = array_merge(self::$defaults, $config);

        //The Ri class will be only initialized once
        Ri::init($this->config);

        self::contextRegister($this);

        //Load context module
        $context_module = $this->loaded_modules[$this->name] = $this->module($this->name);

        $this->storage = new Ri_Storage($this->name);
        $this->storage->session = new Ri_Storage_Session($this->config["session.name"],
                        $this->config["session.sessid_lifetime"], $this->request->clientIp);

        $this->router = new Ri_Router($this->name);

        //load all modules defined in modules.autoload
        foreach ($this->config["modules.autoload"] as $moduleName) {
            if ($moduleName != $this->name) {
                $this->module($moduleName);
            }
        }

        //resolve request resource
        $this->router->resolve();

        //initialize all modules defined in modules.autoload
        foreach ($this->loaded_modules as $moduleName => $mod) {
            if ($moduleName != $this->name) {
                $mod->init();
            }
        }

        //initialize context module
        $context_module->init();

        $this->hookApply("ri.on.create", &$this);
    }

    public function autoload($class_name) {
        $result = Ri::classFind($class_name, $this->loaded_modules);
        // class loaded from /system
        if ($result === true) {
            return true;
        } elseif ($result instanceof Ri_Module) {
            // class loaded from /app or /modules/*
            $this->current_module_name = $result->name;
            return true;
        }

        // class not found
        return false;
    }

    /**
     * Calls the router and returns the action return value
     */
    public function execute() {
        $this->hookApply('ri.before.execute');
        $action_result = $this->router->call();
        $action_result = $this->hookApply('ri.on.execute', $action_result);
        Ri::globalsRestore();
        return $action_result;
    }

    /**
     * Prints the response
     * @param string $ob Output buffer before call this function
     */
    public function render($ob = "") {
        $before_render = $this->hookApply('ri.before.render', array("body" => $this->response->body(), "ob" => $ob));
        $this->response->body($before_render["body"]);
        $this->response->send();
        $this->hookApply('ri.on.render');
    }

    /**
     *
     * @return Ri_Log
     * @throws RuntimeException
     */
    public function log() {
        if (empty($this->log)) {
            $logclass = $this->config("log.class", "Ri_Log");
            $this->log = new $logclass();
            if (!($this->log instanceof Ri_Log)) {
                Ri::throwException("The log.class must be Ri_Log or extended from it");
            }
        }
        return $this->log;
    }

    public function config($name = null, $default = false, $module = null) {
        if (empty($module)) {
            if (empty($name))
                return $this->config;

            if (isset($this->config[$name])) {
                return $this->config[$name];
            }
        } else {
            if (!isset($this->config["modules.config"][$module]))
                return $default;

            if (empty($name))
                return $this->config["modules.config"][$module];

            if (isset($this->config["modules.config"][$module][$name])) {
                return $this->config["modules.config"][$module][$name];
            }
        }

        return $default;
    }

    public function setConfig($name, $value, $module = null) {
        if (empty($module)) {
            if (!empty($name))
                $this->config[$name] = $value;
            else
                $this->config = $value;
        }else {
            if (!empty($name))
                $this->config["modules.config"][$module][$name] = $value;
            else
                $this->config["modules.config"][$module] = $value;
        }
    }

    ## MODULES

    /**
     *
     * @param string $moduleName
     * @return Ri_Module 
     */
    public function module($moduleName = null) {
        if (empty($moduleName))
            return $this->moduleGetCurrent();
        if (!isset($this->loaded_modules[$moduleName])) {
            $this->loaded_modules[$moduleName] = new Ri_Module($moduleName, $this->name);
        }
        return $this->loaded_modules[$moduleName];
    }

    public function moduleUnload($moduleName) {
        if ($moduleName == $this->name)
            return false;
        if ($this->current_module_name == $moduleName)
            $this->current_module_name = $this->name;

        if (!$this->moduleIsLoaded($moduleName)) {
            unset($this->loaded_modules[$moduleName]);
            return true;
        }
        return false;
    }

    public function moduleIsLoaded($moduleName) {
        return isset($this->loaded_modules[$moduleName]);
    }

    public function modulesLoaded() {
        return $this->loaded_modules;
    }

    /**
     *
     * @return Ri_Module 
     */
    public function moduleGetCurrent() {
        if (count($this->loaded_modules) > 0) {
            if ($this->moduleIsLoaded($this->current_module_name)) {
                return $this->loaded_modules[$this->current_module_name];
            } else {
                return $this->loaded_modules[$this->name];
            }
        }
        return false;
    }

    public function moduleSetCurrent($moduleName) {
        if (!$this->moduleIsLoaded($moduleName)) {
            $this->module($moduleName);
        }
        $this->current_module_name = $moduleName;
    }

    /*     * *** HOOKS **** */

    /**
     * Assign hook
     * @param   string  $name       The hook name
     * @param   mixed   $callable   A callable object
     * @param   int     $priority   The hook priority; 0 = high, 10 = low
     * @return  void
     */
    public function hookRegister($name, $callable, $priority = 10) {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (is_callable($callable)) {
            $this->hooks[$name][(int) $priority][] = $callable;
        }
    }

    /**
     * Invoke hook
     * @param   string  $name       The hook name
     * @param   mixed   $hookArg   (Optional) Argument for hooked functions
     * @return  mixed
     */
    public function hookApply($name, $hookArg = null) {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = array(array());
        }
        if (!empty($this->hooks[$name])) {
            // Sort by priority, low to high, if there's more than one priority
            if (count($this->hooks[$name]) > 1) {
                ksort($this->hooks[$name]);
            }
            foreach ($this->hooks[$name] as $priority) {
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
    public function hookGet($name = null) {
        if (!empty($name)) {
            return isset($this->hooks[(string) $name]) ? $this->hooks[(string) $name] : null;
        } else {
            return $this->hooks;
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
    public function hookClear($name = null) {
        if (!empty($name) && isset($this->hooks[(string) $name])) {
            $this->hooks[(string) $name] = array(array());
        } else {
            foreach ($this->hooks as $key => $value) {
                $this->hooks[$key] = array(array());
            }
        }
    }

    /**
     *
     * @param string $name
     * @return Ri_Context
     * @throws RuntimeException 
     */
    public static function getInstance($name = null) {
        if (empty($name))
            $name = Ri::MAIN_CONTEXT_NAME;
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        } else {
            Ri::throwException("The context '$name' does not exist and cannot be loaded.");
        }
    }

    protected static function contextRegister(Ri_Context &$inst) {
        self::$instances[$inst->name] = $inst;
    }

}