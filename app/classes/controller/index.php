<?php

class Controller_Index extends Ri_Controller_Html {

    public function __default() {
        echo "this won't be echoed";
        $this->setBody("<h1>This is the home page</h1><br>Current module: ".$this->context()->module()->name);
    }

    public function action_say() {
        $this->setBody($this->context()->request->get["msg"]);
    }
    
    public function validate_say(){
        return isset($this->context()->request->get["msg"]);
    }
    
    public function action_test(){
        $this->setBody('<pre>'.htmlspecialchars(print_r($this->context(), true)).'</pre>');
    }
    
    public function test2(){
        $this->setBody("action unreachable");
    }
    
    public function __handle($body = null) {
        parent::__handle("<h1>main 404 ERROR</h1><br>Current module: ".$this->context()->module()->name);
    }

}