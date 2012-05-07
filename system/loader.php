<?php

//Start output buffer
ob_start();

require_once RI_PATH_SYSTEM."functions.php";
require_once RI_PATH_SYSTEM."classes"._DS."ri.php";
require_once RI_PATH_SYSTEM."classes"._DS."ri"._DS."context.php";
require_once RI_PATH_SYSTEM."classes"._DS."ri"._DS."http"._DS."request.php";

$main = new Ri_Context(include RI_PATH_APP . "config.php", Ri_Http_Request::getInstance());
$main->run();