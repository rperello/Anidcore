<?php
/**
 * AC Framework Version
 */
define("RI_VERSION", "0.0.1.3-dev");

/**
 * Define the start time of the application. Used for profiling.
 */
define("RI_START_TIME", microtime(true));

/**
 * Define the memory usage at the start of the application. Used for profiling.
 */
define("RI_START_MEMORY", memory_get_usage());

// Display all errors (strict mode) by default
error_reporting(-1);
ini_set("display_errors", true);

//Set locale to UTF-8 by default
setlocale(LC_ALL, 'en_US.UTF8');

//PHP Strict will show warnings if you don't set a timezone. This tells PHP to use UTC.
//You can change this later using config.php
if ( @date_default_timezone_set(date_default_timezone_get()) === false ) {
    date_default_timezone_set('UTC');
}

define("RI_ENV_CLI", php_sapi_name() == "cli");
define("RI_ENV_PHP_5_3", function_exists("get_called_class"));
define("RI_ENV_PHP_5_4", function_exists("class_uses"));

//PATHS
define("_DS", DIRECTORY_SEPARATOR);
define("RI_PATH", realpath(dirname(__FILE__) . _DS) . _DS);
define("RI_PATH_APP", RI_PATH . "app" . _DS);
define("RI_PATH_LOGS", RI_PATH_APP .'logs'._DS);
define("RI_PATH_DATA", RI_PATH_APP .'data'._DS);
define("RI_PATH_CONTENT", RI_PATH . "content" . _DS);
define("RI_PATH_MODULES", RI_PATH . "modules" . _DS);
define("RI_PATH_SYSTEM", RI_PATH . "system" . _DS);

//Change dir to root dir
chdir(RI_PATH);

//Include RI_PATH in include path
restore_include_path();
set_include_path(get_include_path() . PATH_SEPARATOR . RI_PATH);

//Load the application
require_once RI_PATH_SYSTEM."loader.php";

?>