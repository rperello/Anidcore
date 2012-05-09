<?php

require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "singleton.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "observer.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "context.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "system.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac.php";

class Ac_Loader extends Ac_Singleton {

    public $modules;
    protected $current_module_name;

    protected function __construct() {
        spl_autoload_register(array($this, 'autoload'));
    }

    public function autoload($class_name) {
        if (class_exists($class_name))
            return true;

        $class_file = null;
        $class_name_dir = str_replace("_", _DS, strtolower($class_name));

        /* /modules/<module>/classes/ folder */
        if (isset($this->modules)) {
            foreach ($this->modules as $name => $module) {
                if ($module->hasClasses()) {
                    $class_file = $module->path . "classes" . _DS . $class_name_dir . ".php";
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

}

Ac::__init(array_merge(array(
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
            "session.name" => "phpsessid_" . md5($_SERVER["SCRIPT_FILENAME"]),
            "session.sessid_lifetime" => 180,
            "session.cookie_path" => '/' . preg_replace('/\/index\.php.*/', '/', $_SERVER["SCRIPT_NAME"]),
            "session.cookie_secure" => (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on")),
            "session.cookie_lifetime" => 0,
            "session.gc_maxlifetime" => 1440,
            "session.cache_expire" => 180,
            "session.cache_limiter" => "nocache",
            "logger.enabled" => true,
            "logger.class" => "Ac_Log_File",
            "cache.enabled" => false,
            "cache.class" => "Ac_Storage_Cache_File",
            "key.names" => array(), //extra generated keys
            "modules.config" => array(),
            "modules.autoload" => array(),
            "router.default_controller" => "index",
            "cache.path" => AC_PATH_APP . "cache" . _DS
                )
                , include AC_PATH_APP . "config.php"));