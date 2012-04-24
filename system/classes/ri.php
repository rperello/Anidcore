<?php

spl_autoload_register(array('Ri', 'autoload'));

class Ri{
    /**
     * @var array[Ri]
     */
    protected static $apps = array();
    protected static $defaults;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Ri_Http_Request
     */
    protected $request;

    /**
     * @var Ri_Http_Response
     */
    protected $response;

    /**
     * @var Ri_Http_Router
     */
    protected $router;

    /**
     * @var Ri_View
     */
    protected $view;

    /**
     * @var Ri_Log
     */
    protected $log;

    /**
     * @var array Key-value array of application settings
     */
    protected $config;
    
    /**
     * Registry variables
     * @var array 
     */
    protected $vars;
    
    /**
     * Registry variables that can only be assigned once
     * @var array 
     */
    protected $finals;

    /**
     * @var string The application mode
     */
    protected $mode;

    /**
     * @var array Event hooks
     */
    protected $hooks = array(
        'ri.before' => array(array()),
        'ri.before.router' => array(array()),
        'ri.before.dispatch' => array(array()),
        'ri.after.dispatch' => array(array()),
        'ri.after.router' => array(array()),
        'ri.after' => array(array())
    );

    /**
     * @var array Output filters
     */
    protected $filters = array();
    
    public function __construct($config=array(), Ri_Http_Request $request=NULL) {
        if(!isset($config["name"])){
            $config["name"] = "default";
        }
        
        $this->request = empty($request) ? Ri_Http_Request::getInstance() : $request;
        
        if(empty(self::$defaults)){
            self::$defaults = array(
                "server.charset" => "UTF-8",
                "server.locale" => "en_US.UTF8",
                "server.timezone" => "UTC",
                "server.memory_limit" => "180M",
                "server.upload_max_file_size" => "16M",
                "server.post_max_size" => "16M",
                "server.max_execution_time" => "120", //s
                "server.max_input_time" => "240", //s
                "server.display_errors" => true,
                "server.error_reporting" => -1, //E_ALL & ~E_STRICT; // -1 | E_STRICT
                "server.error_log_file" => RI_PATH_LOGS . 'php_errors.log',

                "default_file_mask"=>0775,

                "log.class"=>"Ri_Log",
            );
        }
        
        $this->config = array_merge(self::$defaults, $config);
        
        $this->configureServer();
        
        self::$apps[$config["name"]]=$this;
    }
    
    protected function configureServer() {
        if (!is_dir(RI_PATH_LOGS))
            mkdir(RI_PATH_LOGS, $this->config("default_file_mask", 0775));
        if (!is_dir(RI_PATH_DATA))
            mkdir(RI_PATH_DATA, $this->config("default_file_mask", 0775));
        if (!is_dir(RI_PATH_CONTENT))
            mkdir(RI_PATH_CONTENT, $this->config("default_file_mask", 0775));

        //  Error reporting
        error_reporting($this->config("server.error_reporting"));
        ini_set("display_errors", $this->config("server.display_errors") ? true : false); //stdout = output, stderr=error log file
        //  PHP environment variables
        if (!ini_get('safe_mode')) {

            set_time_limit($this->config("server.max_execution_time"));
            ini_set("memory_limit", $this->config("server.memory_limit"));
            ini_set("max_execution_time", $this->config("server.max_execution_time"));
            ini_set('upload_max_filesize', $this->config("server.upload_max_file_size"));
            ini_set('post_max_size', $this->config("server.post_max_size"));
            ini_set('max_input_time', $this->config("server.max_input_time"));
            ini_set('default_charset', $this->config("server.charset"));
            setlocale(LC_ALL, $this->config("server.locale"));
            date_default_timezone_set($this->config("server.timezone"));
            ini_set("log_errors", true);
            ini_set('error_log', $this->config("server.error_log_file"));
            ini_set("session.use_cookies", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_path", '/'.$this->request->baseDir);
            ini_set("session.use_trans_sid", 0); # do not use PHPSESSID in urls
            ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)
        } else {
            throw new RuntimeException("Rino FATAL ERROR: Rino Framework cannot be executed under safe_mode");
        }
    }
    
    /**
     *
     * @param string $name
     * @return Ri
     * @throws RuntimeException 
     */
    public static function getInstance($name="default"){
        if(isset(self::$apps[$name])){
            return self::$apps[$name];
        }else{
            throw new RuntimeException("Rino FATAL ERROR: The application '$name' does not exist and cannot be loaded.");
        }
    }

    public static function autoload($class_name) {
        $class_file = NULL;
        $class_name_dir = str_replace("_", _DS, strtolower($class_name));

        /* app/classes folder  */
        $class_file = RI_PATH_APP . "classes" . _DS . $class_name_dir . ".php";
        $result = self::includeClass($class_file, $class_name);
        if ($result !== false) {
            return $result;
        }
        
        //modules

        /* system/classes folder */
        $class_file = RI_PATH_SYSTEM . "classes" . _DS . $class_name_dir . ".php";
        return self::includeClass($class_file, $class_name);
        return $result;
    }

    protected static function includeClass($class_file, $class_name) {
        if (is_readable($class_file)) {
            include_once $class_file;
            return class_exists($class_name, false);
        }
        return false;
    }
    
    /**
     *
     * @return Ri_Log
     */
    public function log(){
        if(empty($this->log)){
            $logclass = $this->config("log.class", "Ri_Log");
            $this->log = new $logclass();
            if(!($this->log instanceof Ri_Log)){
                throw new Exception("Rino ERROR: The log.class must be Ri_Log or extended from it");
            }
        }
        return $this->log;
    }

    public function config($name = NULL, $default = false) {
        if (empty($name))
            return $this->config;

        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        
        return $default;
    }
    
    public function setConfig($name, $value){
        $this->config[$name] = $value;
    }
    
    public function run(){
        header("Content-Type: text/plain");
        print_r($this);
    }
}

/**
 * Alias for Ri::getInstance
 * @param string $name
 * @return Ri
 * @throws RuntimeException 
 */
function ri($name = "default") {
    return Ri::getInstance($name);
}