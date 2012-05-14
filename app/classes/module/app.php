<?php

class Module_App extends Ac_Module {

    public function __construct() {
        Ac::on("AcCreateModule_i18n", function() {
                    Ac::setConfig("available_languages", array("en", "es", "de", "it"), "i18n");
                });
        parent::__construct('app');
    }

}