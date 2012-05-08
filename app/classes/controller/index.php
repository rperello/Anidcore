<?php

class Controller_Index extends Ac_Controller_Html {

    public function __default() {
        echo "this won't be echoed";
        $this->setBody("<h1>This is the home page</h1><br>Current module: " . Ac::module()->name);
    }

    public function action_say() {
        $this->setBody($_GET["msg"]);
    }

    public function validate_say() {
        return isset($_GET["msg"]);
    }

    public function action_test() {
        $this->setBody('<pre>' . htmlspecialchars(print_r(array("ac_documents table"=>Ac::dbc()->findAll("ac_documents"), "environment"=>Ac::environment()), true)) . '</pre>');
    }

    public function test2() {
        $this->setBody("action unreachable");
    }

}