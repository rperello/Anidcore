<?php

/**
 * The context is the basic information
 * that Anidcore needs to resolve the request.
 * 
 * @property string $host
 * @property string $scheme
 * @property string $version
 * @property int $port
 * @property string $host_url
 * @property string $method
 * @property string $directory
 * @property string $resource
 * @property string $resource_format
 * @property string $query_string
 * @property string $raw_input
 * @property string $user_agent
 * @property string $languages
 * @property string $client_ip
 * @property boolean $is_cli
 * @property boolean $is_ajax
 */
class Ac_Context extends Ac_Array {

    /**
     * @var Ac_Context
     */
    protected static $instance;
    protected static $default_server = array(
        'HTTPS' => 'off',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => 80,
        'REQUEST_METHOD' => 'GET',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '',
        'QUERY_STRING' => '',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
        'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        'HTTP_USER_AGENT' => 'Unknown',
        'REMOTE_ADDR' => 'localhost',
        'HTTP_X_REQUESTED_WITH' => '',
    );
    protected static $default_settings = array(
        'host' => 'localhost',
        'scheme' => 'http',
        'version' => '1.1',
        'port' => 80,
        'host_url' => 'http://localhost/',
        'method' => 'GET',
        'directory' => '',
        'resource' => '',
        'resource_format' => 'html',
        'query_string' => '',
        'raw_input' => '',
        'user_agent' => 'Unknown',
        'languages' => 'en-us,en',
        'client_ip' => '127.0.0.1',
        'is_cli' => false,
        'is_ajax' => false,
    );

    /**
     * Constructor (private access)
     *
     * @param   array|null  $settings   If present, these are used instead of global server variables
     * @return  void
     */
    private function __construct($settings = null) {
        if (!empty($settings)) {
            $this->vars = array_merge(self::$default_settings, $settings);
        } else {
            $sett = array();
            $_SERVER = array_merge(self::$default_server, $_SERVER);

            /**
             * Server name, domain or hostname 
             */
            $sett['host'] = $_SERVER['SERVER_NAME'];

            /**
             * Scheme and protocol version 
             */
            $protocol = explode('/', trim($_SERVER["SERVER_PROTOCOL"]), 2);
            $https = strtolower($_SERVER["HTTPS"]) == "on" ? true : false;
            $sett["scheme"] = $https ? 'https' : strtolower($protocol[0]);
            $sett["version"] = strtolower($protocol[1]);

            /**
             * Server port 
             */
            $sett["port"] = $_SERVER['SERVER_PORT'];

            $sett["host_url"] = $sett["scheme"] . '://' . $sett["host"] . (($sett["port"] == 80) ? "" : ":" . $sett["port"]) . "/";

            /**
             * Request method (supports method overriding using X_REQUEST_METHOD in POST)
             */
            $sett['method'] = isset($_POST["X_REQUEST_METHOD"]) ? $_POST["X_REQUEST_METHOD"] : $_SERVER['REQUEST_METHOD'];

            /**
             * Directory:
             * Relative directory or subdirectory of the public document root
             */
            if (isset($_SERVER["SCRIPT_NAME"])) {
                $dir = explode("/", trim($_SERVER["SCRIPT_NAME"], "/ "));
                array_pop($dir); //script file, usually index.php
                $dir = implode("/", $dir);
                $dir = (!empty($dir) ? $dir . "/" : null);
            } else {
                $dir = "";
            }
            $sett['directory'] = $dir;

            /*
             * Resource:
             * Request resource (without directory or query string)
             */
            $resource = '';
            if (!empty($_SERVER["PATH_INFO"])) {
                $resource = $_SERVER["PATH_INFO"];
            } else {
                $currentUri = explode("?", $_SERVER["REQUEST_URI"], 2);
                $resource = substr(trim($currentUri[0], "/ "), strlen($sett['directory']));
            }
            $sett['resource'] = explode('.', trim($resource, '/ '));

            /**
             * Requested resource format 
             */
            if (empty($sett['resource']) || (count($sett['resource']) == 1)) {
                $sett['resource'] = implode('.', $sett['resource']);
                $sett['resource_format'] = "html";
            } else {
                $sett['resource_format'] = array_pop($sett['resource']);
                if (empty($sett['resource_format']))
                    $sett['resource_format'] = "html";

                $sett['resource'] = implode('.', $sett['resource']);
            }

            /**
             * Query string 
             */
            $sett['query_string'] = $_SERVER["QUERY_STRING"];

            /**
             * Raw input stream (readable one time only; not available for mutipart/form-data requests)
             */
            $rawInput = @file_get_contents('php://input');
            if (!$rawInput) {
                $rawInput = '';
            }
            $sett['raw_input'] = $rawInput;

            /**
             * Client user agent
             */
            $sett['user_agent'] = $_SERVER["HTTP_USER_AGENT"];

            /**
             * Client keyboard language (comma separated and lowercased) 
             */
            $sett['languages'] = preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

            /**
             * Client IP
             */
            $sett['client_ip'] = in_array($_SERVER["REMOTE_ADDR"], array('::1', '127.0.0.1')) ? 'localhost' : $_SERVER["REMOTE_ADDR"];

            /**
             * Is CLI ? 
             */
            $sett['is_cli'] = (PHP_SAPI == "cli");

            /**
             * Is AJAX ?
             */
            $sett['is_ajax'] = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

            $this->vars = $sett;
        }
    }

    /**
     * Get environment instance (singleton)
     *
     * This creates and/or returns an Context instance (singleton)
     * derived from $_SERVER variables. You may override the global server 
     * variables by using `Environment::mock()` instead.
     *
     * @param   bool            $refresh    Refresh properties using global server variables?
     * @return  Ac_Context
     */
    public static function getInstance($refresh = false) {
        if (is_null(self::$instance) || $refresh) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Get mock environment instance
     *
     * @param   array           $userSettings
     * @return  Ac_Context
     */
    public static function mock($userSettings = array()) {
        self::$instance = new self(array_merge(self::$defaults, $userSettings));
        return self::$instance;
    }

}