<?php

/**
 * Base Anidcore facade class 
 */
class Ac_System {

    const VERSION = "0.5.0";

    ###
    ## RESOLVERS:

    /**
     *
     * @var Ac_Observer 
     */
    protected static $observer;

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
     * @var Ac_Request 
     */
    protected static $request;

    /**
     *
     * @var Ac_Router 
     */
    protected static $router;

    /**
     *
     * @var Ac_Response 
     */
    protected static $response;

    ###
    ## DATA-ACCESS:

    /**
     * 
     * @var stdClass 
     */
    protected static $reg;

    /**
     *
     * @var Ac_Model_Global_Session 
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

    public static function __init() {
        if (empty(self::$observer)) {
            self::$observer = new Ac_Observer();
            //self::$context = Ac_Context::getInstance();
            //self::$loader = Ac_Loader::getInstance();
        }
    }
    
    public static function run($sendResponse = false) {
        if (empty(self::$observer))
            self::__init();
    }
    
    /**
     *
     * @return Ac_Loader 
     */
    public static function loader(){
        return self::$loader;
    }
    
    /**
     *
     * @return Ac_Context 
     */
    public static function context(){
        return self::$context;
    }
    
    /**
     *
     * @return Ac_Observer 
     */
    public static function observer(){
        return self::$observer;
    }
    
    /**
     *
     * @return Ac_Request 
     */
    public static function request(){
        return self::$request;
    }
    
    /**
     *
     * @return Ac_Router
     */
    public static function router(){
        return self::$router;
    }
    
    /**
     *
     * @return Ac_Response 
     */
    public static function response(){
        return self::$response;
    }
    
    /**
     *
     * @return Ac_Response 
     */
    public static function reg(){
        return self::$reg;
    }
    
    /**
     *
     * @return Ac_Model_Global_Session 
     */
    public static function session(){
        return self::$session;
    }
    
    /**
     *
     * @return Ac_Model_Pdo 
     */
    public static function db(){
        return self::$db;
    }
    
    /**
     *
     * @return Ac_Response 
     */
    public static function cache(){
        return self::$cache;
    }
    
    /**
     * Returns the Ac_Log instance or calls the Ac_Log::log()
     * if some parameter is passed
     * @param mixed $data
     * @param string $label
     * @return Ac_Log|void
     */
    public static function log(){
        if(func_num_args() > 0){
            return call_user_func_array(array(self::$log, 'log'), func_get_args());
        }
        return self::$log;
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