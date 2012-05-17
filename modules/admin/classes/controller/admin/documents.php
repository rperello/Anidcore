<?php

class Controller_Admin_Documents extends Ac_Controller_Html {

    public function __index() {
        $this->view->docs = R_Document::find();
        $this->body($this->view->load("pages/documents.php"));
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }
}