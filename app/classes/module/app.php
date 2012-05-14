<?php

class Module_App extends Ac_Module {

    public function __construct() {
        Ac::on("AC_Module_on_create_i18n", function() {
                    Ac::setConfig("available_languages", array("en", "es", "de", "it"), "i18n");
                });
        parent::__construct('app');
    }

}