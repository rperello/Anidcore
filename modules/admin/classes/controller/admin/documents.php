<?php

class Controller_Admin_Documents extends Ac_Controller_Html {

    public function __index() {
        $this->body("<h1>Subcontrollers works!<br></h1><h2>This is the ADMIN DOCUMENTS page</h2><br>Current module: " . print_r(Ac::module(), true) . Ac::module()->name());
    }

    public function __handle() {
        parent::__handle("<h1>ADMIN DOCUMENTS 404 ERROR</h1><br>Current module: " . Ac::module()->name());
    }

    public function action_all() {
        $this->contentType("text/plain");
        $docs = R_Document::find();
        $this->body(json_encode($docs));
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