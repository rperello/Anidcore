<?php

return array(
    "server.timezone" => "Europe/Madrid",
    "logger.enabled" => true,
    "logger.class" => "Ac_Logger",
    "modules.autoload" => array(
        'i18n',
        "admin",
    ),
    "modules.config" => array(
        "admin" => array(
        //override module config. here
        )
    ),
    "database" => array(
        "enabled" => true,
        "instance" => "default",
        "driver" => "mysql",
        "host" => "localhost",
        "port" => 3306,
        "schema" => "anidcore",
        "prefix" => "ac_",
        "username" => "root",
        "password" => "root",
        "charset" => "utf8",
        "collate" => "utf8_general_ci",
        "options" => array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ),
        "autoconnect" => false //false = lazy connect (recommended)
    ),
);