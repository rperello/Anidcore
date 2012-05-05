<?php

abstract class Ri_Controller extends Ri_Environment {

    /**
     * Supported HTTP methods
     * @var array 
     */
    protected $supports = array("GET", "POST");

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
        $this->app()->view->$name = $value;
    }

    public function __get($name) {
        return $this->app()->view->$name;
    }

    public function setBody($body) {
        $this->app()->response->body($body);
    }

    public function setStatus($status) {
        $this->app()->response->status($status);
    }

}