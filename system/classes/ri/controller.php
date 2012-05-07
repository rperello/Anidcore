<?php

abstract class Ri_Controller extends Ri_Environment {

    /**
     * Supported HTTP methods
     * @var array 
     */
    protected $supports = array("GET", "POST");
    
    /**
     * 
     * @var Ri_View 
     */
    protected $view;

    public function __construct($contextName) {
        parent::__construct($contextName);
        $this->view = new Ri_View($contextName);
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

    public function __set($name, $value) {
        $this->view->$name = $value;
    }

    public function __get($name) {
        return $this->view->$name;
    }

    public function setBody($body) {
        $this->context()->response->body($body);
    }

    public function setStatus($status) {
        $this->context()->response->status($status);
    }

}