<?php

class Controller_Index extends Ac_Controller_Html {

    public function __default() {
        //echo "this won't be echoed";
        $this->body("<h1>This is the home page</h1><br>Current module: " . Ac::module()->name());
    }

    public function action_say() {
        $this->body($_GET["msg"]);
    }

    public function validate_say() {
        return isset($_GET["msg"]);
    }

    public function test2() {
        $this->body("action unreachable");
    }

}