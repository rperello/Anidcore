<?php

class Ac_Controller_Html extends Ac_Controller {

    public function __construct() {
        parent::__construct();
        $this->title = "Anidcore Framework v" . Ac::VERSION;
        $this->meta_description = "";
        $this->meta_keywords = "";
        $this->meta_robots = "INDEX,FOLLOW";
        $this->link_canonical = Ac::request()->resourceUri;

        $this->setStatus(200);
    }

    public function __default() {
        $this->setBody($this->title);
    }

    public function __handle() {
        $this->error();
    }

    protected function error($body = null) {
        $this->meta_robots = "NOINDEX,NOFOLLOW";

        if (Ac::response()->status() == 405) {
            $this->setBody(($body === null) ? "<html><body><h1>405 Method Not Allowed</h1>Current module: " . Ac::module()->name . "</body></html>" : $body);
        } else {
            $this->setBody(($body === null) ? "<html><body><h1>404 Not Found</h1>Current module: " . Ac::module()->name . "</body></html>" : $body);
            $this->setStatus(404);
        }
    }

    final private function action_error() {
        
    }

}

?>