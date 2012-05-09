<?php

class Module_App extends Ac_Module {

    public function __construct() {
        parent::__construct('app');
        Ac::hookRegister(Ac::HOOK_ON_ROUTER_RESOLVE, array($this, "beforeRouterResolve"));
        Ac::hookRegister(Ac::HOOK_ON_LOAD_MODULE . "_i18n", array($this, "onLoadModuleI18n"));
    }

    public function onLoadModuleI18n() {
        Ac::setConfig("available_languages", array("en", "es", "de", "it"), "i18n");
    }

    public function beforeRouterResolve() {
        $rs = explode('/', Ac::request()->resource());
        if (ac_arr_last($rs) == 'test2') {
            Ac_Context::getInstance()->method = "PUT";
        }
    }

    public function init() {
        //init module here
        parent::init();
    }

}