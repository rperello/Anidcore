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
 * @property string $format
 * @property string $query_string
 * @property string $raw_input
 * @property string $user_agent
 * @property string $languages
 * @property string $client_ip
 * @property boolean $is_cli
 * @property boolean $is_ajax
 */
class Ac_Context extends Ac_Singleton {

    protected static $default_server = array(
        'HTTPS' => 'off',
        'SERVER_NAME' => 'localhost',
        'SERVER_PORT' => 80,
        'REQUEST_METHOD' => 'GET',
        'SCRIPT_FILENAME' => '',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '',
        'QUERY_STRING' => '',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
        'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
        'HTTP_USER_AGENT' => 'Unknown',
        'REMOTE_ADDR' => 'localhost',
        'HTTP_X_REQUESTED_WITH' => '',
        'HTTP_ORIGIN' => '',
    );
    protected static $default_context = array(
        'id' => '',
        'time' => 0,
        'host' => 'localhost',
        'scheme' => 'http',
        'version' => '1.1',
        'port' => 80,
        'host_url' => 'http://localhost/',
        'origin' => '',
        'method' => 'GET',
        'directory' => '',
        'resource' => '',
        'format' => 'html',
        'query_string' => '',
        'raw_input' => '',
        'user_agent' => 'Unknown',
        'languages' => 'en-us,en',
        'client_ip' => '127.0.0.1',
        'is_cli' => false,
        'is_ajax' => false,
    );

    /**
     *
     * @var array 
     */
    protected $context = array();

    /**
     * Constructor (protected access)
     *
     * @param   array|null  $context   If present, these are used instead of global server variables
     * @return  void
     */
    protected function __construct(array $context = null) {
        if (!empty($context)) {
            $this->context = array_merge(self::$default_context, $context);
        } else {
            $con = array();
            $_SERVER = array_merge(self::$default_server, $_SERVER);

            /**
             * In some HTTP servers DOCUMENT_ROOT points to a unaccessible directory or is not set
             */
            $_SERVER['DOCUMENT_ROOT'] = substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']));

            /**
             * Server name, domain or hostname 
             */
            $con['host'] = $_SERVER['SERVER_NAME'];

            /**
             * Scheme and protocol version 
             */
            $protocol = explode('/', trim($_SERVER["SERVER_PROTOCOL"]), 2);
            $https = strtolower($_SERVER["HTTPS"]) == "on" ? true : false;
            $con["scheme"] = $https ? 'https' : strtolower($protocol[0]);
            $con["version"] = strtolower($protocol[1]);

            /**
             * Server port 
             */
            $con["port"] = $_SERVER['SERVER_PORT'];

            $con["host_url"] = $con["scheme"] . '://' . $con["host"] . (($con["port"] == 80) ? "" : ":" . $con["port"]) . "/";

            /**
             * Request origin (sent by modern browsers in preflight Cross Domain requests) 
             */
            $con["origin"] = $_SERVER["HTTP_ORIGIN"];

            /**
             * Request method (supports method overriding using X_REQUEST_METHOD in POST)
             */
            $con['method'] = isset($_POST["X_REQUEST_METHOD"]) ? $_POST["X_REQUEST_METHOD"] : $_SERVER['REQUEST_METHOD'];

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
            $con['directory'] = $dir;

            /*
             * Resource:
             * Request resource (without directory or query string)
             */
            $resource = '';
            if (!empty($_SERVER["PATH_INFO"])) {
                $resource = $_SERVER["PATH_INFO"];
            } else {
                $currentUri = explode("?", $_SERVER["REQUEST_URI"], 2);
                $resource = substr(trim($currentUri[0], "/ "), strlen($con['directory']));
            }
            $con['resource'] = explode('.', trim($resource, '/ '));

            /**
             * Requested resource format 
             */
            if (empty($con['resource']) || (count($con['resource']) == 1)) {
                $con['resource'] = implode('.', $con['resource']);
                $con['format'] = Ac::config("http.default_format", "html");
            } else {
                $con['format'] = strtolower(array_pop($con['resource']));
                if (empty($con['format'])) {
                    $con['format'] = Ac::config("http.default_format", "html");
                }

                $con['resource'] = implode('.', $con['resource']);
            }

            /**
             * Query string 
             */
            $con['query_string'] = $_SERVER["QUERY_STRING"];

            /**
             * Raw input stream (readable one time only; not available for mutipart/form-data requests)
             */
            $rawInput = @file_get_contents('php://input');
            if (!$rawInput) {
                $rawInput = '';
            }
            $con['raw_input'] = $rawInput;

            /**
             * Client user agent
             */
            $con['user_agent'] = $_SERVER["HTTP_USER_AGENT"];

            /**
             * Client keyboard language (comma separated and lowercased) 
             */
            $con['languages'] = preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

            /**
             * Client IP
             */
            $con['client_ip'] = in_array($_SERVER["REMOTE_ADDR"], array('::1', '127.0.0.1')) ? 'localhost' : $_SERVER["REMOTE_ADDR"];

            /**
             * Is CLI ? 
             */
            $con['is_cli'] = (PHP_SAPI == "cli");

            /**
             * Is AJAX ?
             */
            $con['is_ajax'] = (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

            $this->context = $con;

            $this->generateId();
        }
    }

    public function __get($name) {
        return $this->context[$name];
    }

    public function __set($name, $value) {
        $this->context[$name] = $value;
        $this->generateId();
    }

    protected function generateId() {
        unset($this->context["id"]);
        $this->context["id"] = md5(json_encode($this->context));
        return $this->context["id"];
    }

    /**
     * Changes and reinitializes the current context
     *
     * @param   array           $context
     * @return  Ac_Context      The new context
     */
    public static function parse(array $context = array()) {
        self::$instance = new self(array_merge(self::$default_context, $context));
        return self::$instance;
    }

}