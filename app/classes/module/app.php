<?php

class Module_App extends Ac_Module {

    public function __construct() {
        Ac::on("AcRouterResolve", array($this, "onRouterResolve"));
        Ac::on("AcCreateModule_i18n", array($this, "onCreateModuleI18n"));
        parent::__construct('app');
    }

    public function onCreateModuleI18n() {
        Ac::setConfig("available_languages", array("en", "es", "de", "it"), "i18n");
    }

    public function onRouterResolve() {
        $rs = explode('/', Ac::request()->resource());
        if (ac_arr_last($rs) == 'test2') {
            Ac::context()->method = "PUT";
        }
    }

}