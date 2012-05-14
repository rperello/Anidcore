<?php

class Module_Admin extends Ac_Module {

    public function __construct() {
        $groups = array(
            "developers" => array(1000),
            "administrators" => array(1000, 1001),
            "users" => array(1000, 1001, 1002)
        );

        $defaults = array(
            "groups" => $groups,
            "privileges" => array(
                "site_access" => 'all',
                "disabled_langs" => $groups["administrators"],
                "admin_access" => $groups["administrators"],
                "admin_advanced_fields" => $groups["developers"],
                "admin_global_create" => $groups["administrators"],
                "admin_global_update" => $groups["administrators"],
                "admin_global_delete" => $groups["administrators"],
            )
        );
        Ac::on("Ac_Router_on_resolve", function(){
            if (!defined("AC_ADMIN_URL")) {
                define("AC_ADMIN_URL", Ac::module("admin")->url());
            }
        });
        parent::__construct('admin', $defaults);
    }

}