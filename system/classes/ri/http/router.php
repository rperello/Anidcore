<?php

class Ri_Http_Router {
    /**
     *
     * @var Ri_Http_Router
     */
    protected static $instance;
    
    /**
     *
     * @var Ri_Http_Request 
     */
    public $request;
    
    /**
     *
     * @var Ri_Http_Response 
     */
    public $response;
    
    public function __construct(Ri_Http_Request $request=NULL) {
        $this->request = empty($request) ? Ri_Http_Request::getInstance() : $request;
        $this->response = new Ri_Http_Response();
    }

    /**
     * Router for original request
     * @return Ri_Http_Router 
     */
    public static function getInstance($reset = false) {
        if ((!isset(self::$instance)) || $reset) {
            if($reset){
                self::$instance = new Ri_Http_Router(Ri_Http_Request::getInstance(true));
            }else{
                self::$instance = new Ri_Http_Router();
            }
        }
        return self::$instance;
    }
}