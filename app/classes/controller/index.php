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
        $this->body($this->view->load("pages/home.php"));
    }

    public function action_demo() {
        $this->body($this->view->load("pages/demo.php"));
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }

}