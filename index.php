<?php

/**
 * DIRECTORY_SEPARATOR alias
 */
define("_DS", DIRECTORY_SEPARATOR);

/**
 * Site root path
 */
define("AC_PATH", realpath(dirname(__FILE__) . _DS) . _DS);

/**
 * Path of the app main module 
 */
define("AC_PATH_APP", AC_PATH . "app" . _DS);

/**
 * Logs path
 */
define("AC_PATH_LOGS", AC_PATH_APP . 'logs' . _DS);

/**
 * Data path (sqlite dbs, xml, etc)
 */
define("AC_PATH_DATA", AC_PATH_APP . 'data' . _DS);

/**
 * Static content path (common assets, uploads, ...) 
 */
define("AC_PATH_CONTENT", AC_PATH . "content" . _DS);

/**
 * Modules path
 */
define("AC_PATH_MODULES", AC_PATH . "modules" . _DS);

/**
 * Path of the Anidcore library
 */
define("AC_PATH_SYSTEM", AC_PATH . "system" . _DS);

/**
 * Define the start time of the application. Used for profiling.
 */
define("AC_START_TIME", isset($_SERVER["REQUEST_TIME_FLOAT"]) ? $_SERVER["REQUEST_TIME_FLOAT"] : microtime(TRUE));

/**
 * Define the memory usage at the start of the application. Used for profiling.
 */
define("AC_START_MEMORY", memory_get_usage());

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @see  http://php.net/error_reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Display errors by default 
 */
ini_set("display_errors", true);

/**
 * Set default charset, locale and timezone 
 */
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'en_US.UTF8');
date_default_timezone_set('UTC');

//Check PHP 5.3
if (version_compare(PHP_VERSION, "5.3", "<")) {
    throw new RuntimeException("Anidcore Framework needs PHP 5.3 or greater in order to run");
}

// Change dir to root dir
chdir(AC_PATH);

// Include AC_PATH in include path
restore_include_path();
set_include_path(get_include_path() . PATH_SEPARATOR . AC_PATH);

// Load the core
require_once AC_PATH_SYSTEM . "functions.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "loader.php";

if (is_readable(AC_PATH_APP . "install.php")) {
    require_once AC_PATH_APP . "install.php";
} else {
    Ac::run(true);
}
?>