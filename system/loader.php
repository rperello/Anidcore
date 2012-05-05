<?php

//Start output buffer
ob_start();

require_once RI_PATH_SYSTEM."functions.php";
require_once RI_PATH_SYSTEM."classes"._DS."ri"._DS."application.php";

$app = new Ri_Application(include RI_PATH_APP . "config.php", Ri_Http_Request::getInstance());
$app->run();