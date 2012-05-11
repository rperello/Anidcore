<?php

class Controller_Index extends Ac_Controller_Html {

    /**
     * Supported HTTP methods and their resource formats
     * 
     * For support all formats the value will be '*',
     * for specific formats, the value will be an array containing the supported
     * format extensions i.e. array("html", 'json', 'xml')
     * 
     * @var array 
     */
    protected $supports = array("HEAD" => array('html'), "GET" => array('html', 'json'), "POST" => array('html'));

    public function __default() {
        //echo "this won't be echoed";

        $doc = new R_Document();
        $doc->document_id = 7;
        $doc->content = "<h1>This is the home page</h1><br>Current module: " . Ac::module()->name();

        if (Ac::request()->format() == 'json') {
            $this->contentType("text/json");
            $this->body(json_encode($doc->properties()));
        } else {
            $this->contentType("text/html");
            $this->body($doc->content);
        }
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