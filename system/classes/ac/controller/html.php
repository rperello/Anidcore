<?php

class Ac_Controller_Html extends Ac_Controller {

    public function __construct() {
        parent::__construct();
        $this->contentType("text/html");
        $this->title = Ac::request()->directory();
        $this->meta_description = "";
        $this->meta_keywords = "";
        $this->meta_robots = "INDEX,FOLLOW";
        $this->link_canonical = Ac::request()->resourceUrl();
        $this->status(200);
    }

    public function __index() {
        $this->body($this->title);
    }

    public function __handle() {
        $this->meta_robots = "NOINDEX,NOFOLLOW";
        if (Ac::response()->isSuccessful()) {
            Ac::response()->status(404);
        }
        
        $this->body((func_num_args() > 0) ? print_r(func_get_arg(0), true) : "<html><head></head><body><h1>" . Ac::response()->getStatusMessage() . "</h1></body></html>");
    }

}

?>