<?php

abstract class Ac_Controller {

    /**
     * Supported HTTP methods
     * @var array 
     */
    protected $supports = array("GET", "POST");

    /**
     * 
     * @var Ac_View 
     */
    protected $view;

    public function __construct() {
        $this->view = new Ac_View();
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
    public function __validate() {
        if (!in_array(Ac::request()->requestMethod, $this->supports)) {
            $this->setStatus(405);
            return false;
        }
        return true;
    }

    public function __set($name, $value) {
        $this->view->$name = $value;
    }

    public function __get($name) {
        return $this->view->$name;
    }

    public function setBody($body) {
        Ac::response()->body($body);
    }

    public function setStatus($status) {
        Ac::response()->status($status);
    }

}