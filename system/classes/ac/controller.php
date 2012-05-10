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
        if (!in_array(Ac::request()->method(), $this->supports)) {
            $this->status(405);
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

    protected function body($body) {
        Ac::response()->body($body);
    }

    protected function status($status) {
        Ac::response()->status($status);
    }

}