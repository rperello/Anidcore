<?php

class Controller_Admin extends Ri_Controller_Html {

    public function __default() {
        $this->setBody("<h1>This is the ADMIN page</h1><br>Current module: ".$this->context()->module()->name);
    }
    
    public function __handle($body = null) {
        parent::__handle("<h1>ADMIN 404 ERROR</h1><br>Current module: ".$this->context()->module()->name);
    }

}