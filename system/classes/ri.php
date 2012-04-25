<?php

spl_autoload_register(array('Ri', 'autoload'));

class Ri{
    const CHARS_HEXADECIMAL = "abcdef0123456789";
    const CHARS_ALPHANUMERIC = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    const CHARS_SYMBOLS = "!%&/()=?*+[]{}-_.,:;<>|@#";

    /**
     * @var array[Ri]
     */
    protected static $apps = array();
    protected static $defaults;
    protected static $timers=array();
    protected static $serverConfigured=false;

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
     * @var Ri_Router
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
        'ri.before.create' => array(array()),
        'ri.on.create' => array(array()),
        'ri.before.render' => array(array()),
        'ri.on.render' => array(array()),
        'ri.before.run' => array(array()),
        'ri.on.run' => array(array()),
    );
    
    public function __construct($config=array(), Ri_Http_Request $request=NULL) {
        $hook = $this->hookApply("ri.before.create", array("config"=>$config, "request"=>$request));
        extract($hook);
        
        if(!isset($config["name"])){
            $config["name"] = "default";
        }
        
        $this->request = empty($request) ? Ri_Http_Request::getInstance() : $request;
        $this->response = new Ri_Http_Response();
        
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
                
                
                "session.cookie_path" => '/' . $this->request->baseDir,
                "session.cookie_secure" => ($this->request->scheme == "https"),
                "session.cookie_lifetime" => 0,

                "default_file_mask"=>0775,

                "log.class"=>"Ri_Log",
                
                "key.names"=>array("AUTH_SALT") //extra generated keys
            );
        }
        
        $this->config = array_merge(self::$defaults, $config);
        
        //Server and keys are only configured once
        if(!self::$serverConfigured){
            self::configureServer($this->config);
            self::generateKeys($this->config);
            self::$serverConfigured = true;
        }
        
        $this->router = new Ri_Router($this);
        
        $this = $this->hookApply("ri.on.create", $this);
        
        self::$apps[$this->config["name"]]=$this;
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

    /**
     * Gets / sets an application variable
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, sets the value of a var)
     * @return mixed 
     */
    public static function vars($varname = NULL) {
        if (empty($varname))
            return $this->vars;

        $args = func_get_args();
        if (count($args) == 2) {
            $this->vars[$varname] = $args[1];
            return null;
        } else {
            if (!isset($this->vars[$varname]))
                return false;
            else
                return $this->vars[$varname];
        }
    }

    /**
     * Gets / sets an application variable that can only be assigned once (final vars, pseudo constants)
     * @param string $varname (if empty, returns all vars)
     * @param mixed $new_value (if not empty, assings a value to a non-existing var)
     * @return mixed 
     */
    public static function finals($varname = NULL) {
        if (empty($varname))
            return $this->finals;

        $args = func_get_args();
        if (count($args) == 2) {
            if (!isset($this->finals[$varname]))
                $this->finals[$varname] = $args[1];
            return null;
        } else {
            if (!isset($this->finals[$varname]))
                return false;
            else
                return $this->finals[$varname];
        }
    }
    
    public function setConfig($name, $value){
        $this->config[$name] = $value;
    }

    /***** ACCESSORS *****/

    /**
     * Get the Request object
     * @return Ri_Http_Request
     */
    public function request() {
        return $this->request;
    }

    /**
     * Get the Response object
     * @return Ri_Http_Response
     */
    public function response() {
        return $this->response;
    }

    /**
     * Get the Router object
     * @return Ri_Router
     */
    public function router() {
        return $this->router;
    }
    
    
    

    /***** HOOKS *****/

    /**
     * Assign hook
     * @param   string  $name       The hook name
     * @param   mixed   $callable   A callable object
     * @param   int     $priority   The hook priority; 0 = high, 10 = low
     * @return  void
     */
    public function hookRegister( $name, $callable, $priority = 10 ) {
        if ( !isset($this->hooks[$name]) ) {
            $this->hooks[$name] = array(array());
        }
        if ( is_callable($callable) ) {
            $this->hooks[$name][(int)$priority][] = $callable;
        }
    }

    /**
     * Invoke hook
     * @param   string  $name       The hook name
     * @param   mixed   $hookArg   (Optional) Argument for hooked functions
     * @return  mixed
     */
    public function hookApply( $name, $hookArg = null ) {
        if ( !isset($this->hooks[$name]) ) {
            $this->hooks[$name] = array(array());
        }
        if( !empty($this->hooks[$name]) ) {
            // Sort by priority, low to high, if there's more than one priority
            if ( count($this->hooks[$name]) > 1 ) {
                ksort($this->hooks[$name]);
            }
            foreach( $this->hooks[$name] as $priority ) {
                if( !empty($priority) ) {
                    foreach($priority as $callable) {
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
    public function hookGet( $name = null ) {
        if ( !empty($name) ) {
            return isset($this->hooks[(string)$name]) ? $this->hooks[(string)$name] : null;
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
    public function hookClear( $name = null ) {
        if ( !empty($name) && isset($this->hooks[(string)$name]) ) {
            $this->hooks[(string)$name] = array(array());
        } else {
            foreach( $this->hooks as $key => $value ) {
                $this->hooks[$key] = array(array());
            }
        }
    }
    
    /**
     * Renders a view 
     */
    public function render(){
        $this->hookApply('ri.before.render');
        header("Content-Type: text/plain");
        print_r($this);
        $this->hookApply('ri.after.render');
    }
    
    /**
     * Runs the app 
     */
    public function run(){
        $this->hookApply('ri.before.run');
        header("Content-Type: text/plain");
        print_r($this);
        $this->hookApply('ri.after.run');
    }
    
    protected static function generateKeys($config){
        //Security Keys
        $keys_file = RI_PATH_DATA . "keys.data";
        if (!is_readable($keys_file)) {
            $security_keys = array(
                "key.GLOBAL_SALT" => ri_str_random(64, self::CHARS_ALPHANUMERIC.self::CHARS_SYMBOLS)
            );
            foreach($config["key.names"] as $i => $kn){
                $security_keys["key.".$kn] = ri_str_random(64, self::CHARS_ALPHANUMERIC.self::CHARS_SYMBOLS);
            }
            file_put_contents($keys_file, serialize($security_keys));
        } else {
            $security_keys = unserialize(file_get_contents($keys_file));
        }
        
        foreach($security_keys as $k => $val){
            $config[$k]=$val;
        }
    }
    
    protected static function configureServer($config) {
        if (!is_dir(RI_PATH_LOGS))
            mkdir(RI_PATH_LOGS, $config["default_file_mask"]);
        if (!is_dir(RI_PATH_DATA))
            mkdir(RI_PATH_DATA, $config["default_file_mask"]);
        if (!is_dir(RI_PATH_CONTENT))
            mkdir(RI_PATH_CONTENT, $config["default_file_mask"]);

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
            ini_set('default_charset', $config["server.charset"]);
            setlocale(LC_ALL, $config["server.locale"]);
            date_default_timezone_set($config["server.timezone"]);
            ini_set("log_errors", true);
            ini_set('error_log', $config["server.error_log_file"]);
            ini_set("session.use_cookies", 1);
            ini_set("session.use_only_cookies", 1);
            ini_set("session.cookie_path", $config["session.cookie_path"]);
            ini_set("session.cookie_secure", $config["session.cookie_secure"]);
            ini_set("session.cookie_lifetime", $config["session.cookie_lifetime"]);
            ini_set("session.use_trans_sid", 0); # do not use PHPSESSID in urls
            ini_set("session.hash_function", 1); # use sha1 algorithm (160 bits)
        } else {
            throw new RuntimeException("Rino FATAL ERROR: Rino Framework cannot be executed under safe_mode");
        }
    }

    public static function timerStart() {
        self::$timers[] = microtime(true);
    }

    public static function timerStop($start_time = NULL, $detailed_result = true) {
        if ($start_time == NULL) {
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