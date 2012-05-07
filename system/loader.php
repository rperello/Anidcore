<?php

require_once RI_PATH_SYSTEM . "functions.php";
require_once RI_PATH_SYSTEM . "classes" . _DS . "ri.php";
require_once RI_PATH_SYSTEM . "classes" . _DS . "ri" . _DS . "object.php";
require_once RI_PATH_SYSTEM . "classes" . _DS . "ri" . _DS . "context.php";
require_once RI_PATH_SYSTEM . "classes" . _DS . "ri" . _DS . "http" . _DS . "request.php";


Ri::call(include RI_PATH_APP . "config.php", new Ri_Http_Request($_SERVER), null, true);