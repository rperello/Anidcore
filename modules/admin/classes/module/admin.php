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
        parent::__construct('admin', $defaults);
    }

}