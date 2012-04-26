<?php

abstract class Ri_Controller {

    /**
     * Supported HTTP methods
     * @var array 
     */
    protected $supports = array("GET", "POST");
    
    /**
     *
     * @var Ri 
     */
    protected $app;
    
    public function __construct(Ri &$app) {
        $this->app = $app;
    }

    /**
     * Default function 
     */
    abstract public function __default();

    /**
     * Error handler function 
     */
    abstract public function __handle();

    /**
     * Request validation function
     */
    abstract public function __validate();
}