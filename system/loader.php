<?php

//Start output buffer
ob_start();

//Change dir to root dir
chdir(RI_PATH);

//Include RI_PATH in include path
restore_include_path();
set_include_path(get_include_path() . PATH_SEPARATOR . RI_PATH);

$config = array_merge(include RI_PATH_SYSTEM . "defaults.php", include RI_PATH_APP . "config.php");

$app = new Ri($config);

