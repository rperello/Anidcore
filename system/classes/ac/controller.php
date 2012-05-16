<?php

abstract class Ac_Controller {

    /**
     * Supported HTTP methods and their resource formats
     * 
     * For support all formats the value will be '*',
     * for specific formats, the value will be an array containing the supported
     * format extensions i.e. array("html", 'json', 'xml')
     * 
     * @var array 
     */
    protected $supports = array("HEAD" => '*', "GET" => '*', "POST" => '*');

    /**
     * 
     * @var Ac_View 
     */
    protected $view;

    public function __construct() {
        $this->contentType("text/plain");
        $this->status(200);
        $view_class = Ac::config("views.class", "Ac_View");
        $this->view = new $view_class();
    }

    /**
     * Default function 
     */
    abstract public function __index();

    /**
     * Error handler function 
     */
    abstract public function __handle();

    /**
     * Request validation function
     */
    public function __validate($action = null) {
        $method = Ac::request()->method();
        if (!isset($this->supports[$method])) {
            $this->status(405);
            return false;
        } elseif (($this->supports[$method] != '*') && (!in_array(Ac::request()->format(), $this->supports[$method]))) {
            $this->status(415);
            return false;
        }
        return true;
    }

    /**
     * This action should not be callable
     * @return mixed 
     */
    final private function action_error() {
        return $this->__handle();
    }

    public function __set($name, $value) {
        $this->view->$name = $value;
    }

    public function __get($name) {
        return $this->view->$name;
    }

    protected function contentType($contentType) {
        Ac::response()->contentType($contentType);
    }

    protected function body($body) {
        Ac::response()->body($body);
    }

    protected function status($status) {
        Ac::response()->status($status);
    }

}