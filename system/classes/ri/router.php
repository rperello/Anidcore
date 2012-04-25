<?php

class Ri_Router {
    /**
     *
     * @var Ri_Router
     */
    protected static $instance;
    
    /**
     *
     * @var Ri
     */
    public $app;
    
    public function __construct(Ri &$app=NULL) {
        if(empty($app)) $app = Ri::getInstance();
        $this->app = $app;
    }

    /**
     * Router for original request and default app
     * @return Ri_Router 
     */
    public static function getInstance($reset = false) {
        if ((!isset(self::$instance)) || $reset) {
            self::$instance = new Ri_Router();
        }
        return self::$instance;
    }
}