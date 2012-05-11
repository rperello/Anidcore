<?php

class Controller_Admin extends Ac_Controller_Html {

    public function __default() {
        $this->body("<h1>This is the ADMIN page</h1><br>Current module: " . print_r(Ac::module(), true).Ac::module()->name());
    }

    public function __handle() {
        parent::__handle("<h1>ADMIN 404 ERROR</h1><br>Current module: " . Ac::module()->name());
    }

    public function action_test() {
        $this->body('<pre>' . htmlspecialchars(print_r(
                                array(
                            "module" => Ac::module()->name(),
                            "controller" => Ac::router()->controller(),
                            "action" => Ac::router()->action(),
                            "module_url" => Ac::url(),
                            //"ac_documents table" => Ac::dbc()->findAll("ac_documents"),
                            "context" => Ac::context()), true)
                ) . '</pre>');
    }
}