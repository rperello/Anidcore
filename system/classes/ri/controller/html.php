<?php

class Ri_Controller_Html extends Ri_Controller {

    public function __construct($contextName) {
        parent::__construct($contextName);
        $this->title = "Rino Framework v" . Ri::VERSION;
        $this->meta_description = "";
        $this->meta_keywords = "";
        $this->meta_robots = "INDEX,FOLLOW";
        $this->link_canonical = $this->context()->request->currentUri(true);

        $this->setStatus(200);
    }

    public function __default() {
        $this->setBody($this->title);
    }

    public function __handle($body = null) {
        $this->error($body);
    }

    protected function error($body = null) {
        $this->meta_robots = "NOINDEX,NOFOLLOW";

        if ($this->context()->response->status() == 405) {
            $this->setBody(($body === null) ? "<html><body><h1>405 Method Not Allowed</h1></body></html>" : $body);
        } else {
            $this->setBody(($body === null) ? "<html><body><h1>404 Not Found</h1></body></html>" : $body);
            $this->setStatus(404);
        }
    }

    final private function action_error() {
        
    }

}

?>