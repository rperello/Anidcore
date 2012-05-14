<?php

class Controller_Admin_Documents extends Ac_Controller_Html {

    public function __index() {
        $this->body("<h1>Subcontrollers works!<br></h1>");
    }

    public function __handle() {
        parent::__handle($this->view->load("pages/error.php"));
    }

    public function action_all() {
        $this->contentType("text/plain");
        $docs = R_Document::find();
        $this->body(print_r($docs, true));
        return $docs;
    }

    public function action_test() {
        $this->body('<pre>' . htmlspecialchars(print_r(
                                array(
                            "module" => Ac::module()->name(),
                            "controller" => Ac::router()->controller(),
                            "action" => Ac::router()->action(),
                            "module_url" => Ac::url(),
                            "ac_documents table" => R_Document::find(),
                            "context" => Ac::context()), true)
                ) . '</pre>');
    }

}