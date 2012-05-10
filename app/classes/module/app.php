<?php

class Module_App extends Ac_Module {

    public function __construct() {
        parent::__construct('app');
        
        Ac::on("AcRouterResolve", array($this, "onRouterResolve"));
        Ac::on("AcLoadModule_i18n", array($this, "onLoadModuleI18n"));
    }

    public function onLoadModuleI18n() {
        Ac::setConfig("available_languages", array("en", "es", "de", "it"), "i18n");
    }

    public function onRouterResolve() {
        $rs = explode('/', Ac::request()->resource());
        if (ac_arr_last($rs) == 'test2') {
            Ac::context()->method = "PUT";
        }
    }

    public function init() {
        //init module here
        parent::init();
    }

}