<?php

/**
 * Define the start time of the application. Used for profiling.
 */
define("RI_START_TIME", microtime(true));

/**
 * Define the memory usage at the start of the application. Used for profiling.
 */
define("RI_START_MEMORY", memory_get_usage());

// Default server config
error_reporting(-1);
ini_set("display_errors", true);
setlocale(LC_ALL, 'en_US.UTF8');
date_default_timezone_set('UTC');

// Paths
define("_DS", DIRECTORY_SEPARATOR);
define("AC_PATH", realpath(dirname(__FILE__) . _DS) . _DS);
define("AC_PATH_APP", AC_PATH . "app" . _DS);
define("AC_PATH_LOGS", AC_PATH_APP . 'logs' . _DS);
define("AC_PATH_DATA", AC_PATH_APP . 'data' . _DS);
define("AC_PATH_CONTENT", AC_PATH . "content" . _DS);
define("AC_PATH_MODULES", AC_PATH . "modules" . _DS);
define("AC_PATH_SYSTEM", AC_PATH . "system" . _DS);

// Change dir to root dir
chdir(AC_PATH);

// Include AC_PATH in include path
restore_include_path();
set_include_path(get_include_path() . PATH_SEPARATOR . AC_PATH);

// Base directory
if (isset($_SERVER["SCRIPT_NAME"])) {
    $baseDir = explode("/", trim($_SERVER["SCRIPT_NAME"], "/ "));
    array_pop($baseDir); //script file, usually index.php
    $baseDir = implode("/", $baseDir);
    $baseDir = (!empty($baseDir) ? $baseDir . "/" : null);
} else {
    $baseDir = "";
}
define("AC_BASEDIR", $baseDir);

ob_start();
//Load the application
require_once AC_PATH_SYSTEM . "functions.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac.php";

Ac::init();
Ac::run(true);

?>