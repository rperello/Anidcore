<?php

class Controller_Index extends Ri_Controller_Html {

    public function __default() {
        $this->setBody("<h1>This is the home page</h1>");
    }

    public function action_hello() {
        $this->setBody("Hello world!");
    }

}