<?php

class Controller_Index extends Ac_Controller_Html {

    /**
     * Supported HTTP methods and their resource formats
     * 
     * For support all formats the value will be '*',
     * for specific formats, the value will be an array containing the supported
     * format extensions i.e. array("html", 'json', 'xml')
     * 
     * @var array 
     */
    protected $supports = array("HEAD" => array('html'), "GET" => array('html'), "POST" => array('html'));

    public function __index() {
//        $this->body("<h1>This is the main page</h1><br>Current module: " . print_r(Ac::module(), true) . Ac::module()->name());
        $this->body($this->view->load("pages/home.php"));
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }

}