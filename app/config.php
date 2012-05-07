<?php

return array(
    "server.timezone" => "Europe/Madrid",
    "modules.config"=>array(
        "admin"=>array(
            //override module config. here
            "foo1"=>"bar1"
        )
    ),
    "modules.autoload" => array(
        "admin", //"firephp"
    )
);