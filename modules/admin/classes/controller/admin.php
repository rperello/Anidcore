<?php

class Controller_Admin extends Ac_Controller_Html {

    public function __index() {
//        $this->body("<h1>This is the main page</h1><br>Current module: " . print_r(Ac::module(), true) . Ac::module()->name());
        $this->body($this->view->load("pages/home.php"));
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }
}