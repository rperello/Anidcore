<?php

require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "singleton.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "observer.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "context.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "system.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac.php";

class Ac_Loader extends Ac_Singleton {

    protected $config;
    protected $modules = array();
    protected $keys = array();
    protected $active_module_name;

    protected function __construct() {
        $this->getConfig();
        $this->generateKeys();
        $this->configureServer();
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     *
     * @return array 
     */
    public function getModules() {
        return $this->modules;
    }

    /**
     *
     * @param string $name
     * @return boolean 
     */
    public function hasModule($name) {
        return isset($this->modules[$name]);
    }

    /**
     *
     * @param string $name
     * @param boolean $autoImport
     * @return boolean 
     */
    public function setActiveModule($name, $autoImport = true) {
        $result = true;
        if (!$this->hasModule($name)) {
            if ($autoImport)
                $result = ($this->loadModule($name) != false);
            else
                $result = false;
        }
        $this->active_module_name = $name;
        return $result;
    }

    /**
     *
     * @param string $name
     * @param boolean $autoImport
     * @return Ac_Module|false 
     */
    public function loadModule($name = null, $autoImport = true) {
        if (empty($name)) {
            if (count($this->modules) > 0) {
                if ($this->hasModule($this->active_module_name)) {
                    return $this->modules[$this->active_module_name];
                }
            }
        }
        if (!isset($this->modules[$name])) {
            if ($autoImport) {
                $this->modules[$name] = Ac_Module::factory($name);
                return $this->modules[$name];
            }
        }else
            return $this->modules[$name];
        return false;
    }

    /**
     * Unsets the module from the loaded modules array,
     * so it would be excluded from the 'autoload class' scope
     * 
     * @param string $name
     * @return boolean 
     */
    public function unloadModule($name) {
        if ($name == "app")
            return false;

        if ($this->hasModule($name)) {
            unset($this->modules[$name]);
            return true;
        }

        if ($name == $this->active_module_name)
            $this->active_module_name = "app";
        return false;
    }

    /**
     * class autoloader
     * @param string $class_name
     * @return Ac_Module|boolean 
     */
    public function autoload($class_name) {
        if (class_exists($class_name, false))
            return true;

        $class_file = null;
        $class_name_dir = str_replace("_", _DS, strtolower($class_name));

        /* /modules/<module>/classes/ folder */
        if (isset($this->modules)) {
            foreach ($this->modules as $name => $module) {
                if ($module->hasAutoload()) {
                    $class_file = $module->path() . "classes" . _DS . $class_name_dir . ".php";
                    $exists = $this->classInclude($class_file, $class_name);
                    if ($exists) {
                        return $module;
                    }
                }
            }
        }

        /* system/classes folder */
        $class_file = AC_PATH_SYSTEM . "classes" . _DS . $class_name_dir . ".php";
        if ($this->classInclude($class_file, $class_name)) {
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
    public function classInclude($class_file, $class_name) {
        if (is_readable($class_file)) {
            include_once $class_file;
            return class_exists($class_name, false);
        }
        return false;
    }

    protected function generateKeys() {
        //Securlty Keys
        $keys_file = AC_PATH_DATA . "keys.data";
        if (!is_readable($keys_file)) {
            $securlty_keys = array(
                "key.GLOBAL_SALT" => ac_str_random(64, AC_CHARS_ALPHANUMERIC
                        . AC_CHARS_SYMBOLS)
            );
            foreach ($this->config["key.names"] as $i => $kn) {
                $securlty_keys["key." . $kn] = ac_str_random(64, AC_CHARS_ALPHANUMERIC
                        . AC_CHARS_SYMBOLS);
            }
            file_put_contents($keys_file, serialize($securlty_keys));
        } else {
            $securlty_keys = unserialize(file_get_contents($keys_file));
        }

        foreach ($securlty_keys as $k => $val) {
            $this->config[$k] = $val;
        }
    }

    /**
     *
     * @param array $config
     * @throws RuntimeException 
     */
    protected function configureServer() {
        if (!is_dir(AC_PATH_LOGS))
            mkdir(AC_PATH_LOGS, 0770);
        if (!is_dir(AC_PATH_DATA))
            mkdir(AC_PATH_DATA, 0770);
        if (!is_dir(AC_PATH_CONTENT))
            mkdir(AC_PATH_CONTENT, 0775);

        //  Error reporting
        error_reporting($this->config["server.error_reporting"]);
        ini_set("display_errors", $this->config["server.display_errors"] ? true : false); //stdout = output, stderr=error log file
        //  PHP environment variables
        if (!ini_get('safe_mode')) {

            set_time_limit($this->config["server.max_execution_time"]);
            ini_set("memory_limit", $this->config["server.memory_limit"]);
            ini_set("max_execution_time", $this->config["server.max_execution_time"]);
            ini_set('upload_max_filesize', $this->config["server.upload_max_file_size"]);
            ini_set('post_max_size', $this->config["server.post_max_size"]);
            ini_set('max_input_time', $this->config["server.max_input_time"]);
            ini_set('default_charset', $this->config["server.default_charset"]);
            ini_set('default_mimetype', $this->config["server.default_mimetype"]);

            setlocale(LC_ALL, $this->config["server.locale"]);
            date_default_timezone_set($this->config["server.timezone"]);

            ini_set("log_errors", true);
            ini_set('error_log', $this->config["server.error_log_file"]);
            $this->config["session.name"] = "phpsessid_" . md5($_SERVER["SCRIPT_FILENAME"] . $this->config["key.GLOBAL_SALT"]);
            ini_set("session.name", $this->config["session.name"]);
            ini_set("session.use_cookies", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_path", $this->config["session.cookie_path"]);
            ini_set("session.cookie_secure", $this->config["session.cookie_secure"]);
            ini_set("session.cookie_lifetime", $this->config["session.cookie_lifetime"]);
            ini_set('session.gc_maxlifetime', $this->config["session.gc_maxlifetime"]);
            ini_set("session.use_trans_sid", 0); # do not use PHPSESSID in urls
            ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)
            session_cache_expire($this->config["session.cache_expire"]);
            session_cache_limiter($this->config["session.cache_limiter"]);
        } else {
            Ac::exception("Anidcore Framework cannot be executed under safe_mode");
        }

        Ac::trigger("AcConfigureServer");
    }

    public function getConfig() {
        if (empty($this->config)) {
            $this->config = Ac::trigger("AcImportConfig", array_merge(array(
                ////
                //MODULES:
                "modules.config" => array(),
                "modules.autoload" => array(),
                ////
                //ROUTER:
                "router.default_controller" => "index",
                ////
                //LOG:
                "log.enabled" => true,
                "log.class" => "Ac_Log_File",
                ////
                //CACHE:
                "cache.enabled" => false,
                "cache.class" => "Ac_Storage_Cache_File",
                "cache.path" => AC_PATH_APP . "cache" . _DS,
                ////
                //KEY:
                "key.names" => array(), //extra generated keys
                ////
                //SERVER:
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
                ////
                //SESSION:
                "session.sessid_lifetime" => 180,
                "session.cookie_path" => preg_replace('/\/index\.php.*/', '/', $_SERVER["SCRIPT_NAME"]),
                "session.cookie_secure" => (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")),
                "session.cookie_lifetime" => 0,
                "session.gc_maxlifetime" => 1440,
                "session.cache_expire" => 180,
                "session.cache_limiter" => "nocache",
                    )
                    , include AC_PATH_APP . "config.php"));
        }
        return $this->config;
    }

}