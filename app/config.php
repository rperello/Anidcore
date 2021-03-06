<?php

defined('AC_PATH') OR exit('No direct access allowed in file ' . __FILE__);

return array(
    "server.timezone" => "Europe/Madrid",
    "log.enabled" => true,
    "log.class" => "Ac_Log_File",
    "modules.autoload" => array(
        'i18n',
        "admin",
    ),
    "modules.config" => array(
        "admin" => array(
        //override module config. here
        ),
        "i18n"=>array(
            "lang_in_urls"=>false,
        ),
    ),
    "database" => array(
        "enabled" => true,
        "instance" => "default",
        "driver" => "mysql",
        "host" => "localhost",
        "port" => 3306,
        "dbname" => "anidcore",
        "prefix" => "ac_",
        "username" => "root",
        "password" => "root",
        "charset" => "utf8",
        "collate" => "utf8_general_ci",
        "options" => array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC),
        "autoconnect" => false, //false = lazy connect (recommended)
        "log_success" => false,
        "log_errors" => true,
    ),
);