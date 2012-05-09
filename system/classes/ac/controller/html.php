<?php

class Ac_Controller_Html extends Ac_Controller {

    public function __construct() {
        parent::__construct();
        $this->title = "Anidcore Framework v" . Ac::VERSION;
        $this->meta_description = "";
        $this->meta_keywords = "";
        $this->meta_robots = "INDEX,FOLLOW";
        $this->link_canonical = Ac::request()->resourceUrl();

        $this->status(200);
    }

    public function __default() {
        $this->body($this->title);
    }

    public function __handle() {
        $this->error();
    }

    protected function error($body = null) {
        $this->meta_robots = "NOINDEX,NOFOLLOW";

        if (Ac::response()->status() == 405) {
            $this->body(($body === null) ? "<html><body><h1>405 Method Not Allowed</h1>Current module: " . Ac::module()->name . "</body></html>" : $body);
        } else {
            $this->body(($body === null) ? "<html><body><h1>404 Not Found</h1>Current module: " . Ac::module()->name . "</body></html>" : $body);
            $this->status(404);
        }
    }

    public function action_test() {
        $this->body('<pre>' . htmlspecialchars(print_r(
                                array(
                            "module" => Ac::module()->name,
                            "controller" => Ac::router()->controller(),
                            "action" => Ac::router()->action(),
                            "module_url" => Ac::url(),
                            "ac_documents table" => Ac::dbc()->findAll("ac_documents"),
                            "environment" => Ac::environment()), true)
                ) . '</pre>');
    }

    final private function action_error() {
        
    }

}

?>