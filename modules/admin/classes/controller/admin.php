<?php

class Controller_Admin extends Ac_Controller_Html {

    public function __index() {
        $this->body($this->view->load("pages/home.php"));
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }
}