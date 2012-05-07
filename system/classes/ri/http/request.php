<?php

class Ri_Http_Request {

    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';
    const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';

    /**
     *
     * @var Ri_Http_Request 
     */
    protected static $instance;

    ## URL DATA:
    /**
     * $_SERVER variables
     * @var Ri_Object 
     */
    public $server;
    public $clientIp;

    /**
     * TCP/IP Server Port
     * @var int 
     */
    public $port;

    /**
     * Base directory
     * @var string 
     */
    public $baseDir;

    /**
     * Requested resource path
     * @var string 
     */
    public $resource;


    ## INPUT DATA:

    /**
     * Language list formatted like 'es-es,es,en-us,en'
     * @var string 
     */
    public $languages;
    public $contentType;
    public $body;

    /**
     *
     * @var Ri_Object 
     */
    public $get;

    /**
     *
     * @var Ri_Object 
     */
    public $post;

    /**
     *
     * @var Ri_Object 
     */
    public $request;

    /**
     *
     * @var Ri_Object 
     */
    public $args;

    /**
     *
     * @var Ri_Object 
     */
    public $env;

    /**
     *
     * @var Ri_Object 
     */
    public $put;

    /**
     *
     * @var Ri_Object 
     */
    public $cookie;

    /**
     *
     * @var Ri_Object 
     */
    public $files;

    public function __construct($server = null) {
        $this->server = empty($server) ? new Ri_Object($_SERVER) : new Ri_Object($server);

        if (!($this->server["SERVER_PROTOCOL"]))
            $this->server["SERVER_PROTOCOL"] = "http";
        if (!($this->server["HTTPS"]))
            $this->server["HTTPS"] = "off";
        if (!($this->server["SERVER_NAME"]))
            $this->server["SERVER_NAME"] = "localhost";

        if (!($this->server["SCRIPT_NAME"]))
            $this->server["SCRIPT_NAME"] = null;
        if (!($this->server["PATH_INFO"]))
            $this->server["PATH_INFO"] = null;
        if (!($this->server["PHP_SELF"]))
            $this->server["PHP_SELF"] = null;

        if (!($this->server["REQUEST_URI"]))
            $this->server["REQUEST_URI"] = null;
        if (!($this->server["QUERY_STRING"]))
            $this->server["QUERY_STRING"] = null;

        if (!($this->server["AUTH_TYPE"]))
            $this->server["AUTH_TYPE"] = null;
        if (!($this->server["PHP_AUTH_USER"]))
            $this->server["PHP_AUTH_USER"] = null;
        if (!($this->server["PHP_AUTH_PW"]))
            $this->server["PHP_AUTH_PW"] = null;
        if (!($this->server["PHP_AUTH_DIGEST"]))
            $this->server["PHP_AUTH_DIGEST"] = null;

        if (!($this->server["HTTP_REFERER"]))
            $this->server["HTTP_REFERER"] = null;
        if (!($this->server["HTTP_USER_AGENT"]))
            $this->server["HTTP_USER_AGENT"] = null;
        if (!($this->server["HTTP_ACCEPT_LANGUAGE"]))
            $this->server["HTTP_ACCEPT_LANGUAGE"] = null;

        if (!($this->server["HTTP_X_REQUESTED_WITH"]))
            $this->server["HTTP_X_REQUESTED_WITH"] = null;

        $this->port = @parse_url($this->server["REQUEST_URI"], PHP_URL_PORT);
        $this->port = !empty($this->port) ? $this->port : 80;

        $this->languages = preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", $this->server["HTTP_ACCEPT_LANGUAGE"]);

        $this->clientIp = $this->getClientIp();

        $this->body = @file_get_contents('php://input');

        $this->get = new Ri_Object($_GET);
        $this->post = new Ri_Object($_POST);
        $this->env = new Ri_Object($_ENV);
        $this->cookie = new Ri_Object($_COOKIE);
        $this->args = new Ri_Object($this->server->get("argv", null));

        $this->files = new Ri_Object("files", $_FILES);

        $this->contentType = $this->getContentType();

        $this->put = new Ri_Object($this->getPut());

        $this->request = new Ri_Object(array_merge($this->cookie->toArray(), $this->put->toArray(), $this->post->toArray(), $this->get->toArray()));

        $this->baseDir = $this->getBaseDir();

        $this->resource = $this->getResource();
    }

    /**
     * Original request data
     * @return Ri_Http_Request 
     */
    public static function getInstance($reset = false) {
        if ((!isset(self::$instance)) || $reset) {
            self::$instance = new Ri_Http_Request($_SERVER);
        }
        return self::$instance;
    }

    /**
     * Returns ["SERVER_PROTOCOL"]
     * @return string 
     */
    public function protocol() {
        return $this->isHttps() ? "https" : 'http';
    }

    /**
     * Returns ["SERVER_NAME"]
     * @return string 
     */
    public function domain() {
        return $this->server["SERVER_NAME"];
    }

    public function domainUri() {
        return $this->protocol() . "://" . $this->domain() . (($this->port == 80) ? "" : ":" . $this->port) . "/";
    }

    public function baseUri() {
        return $this->domainUri() . $this->baseDir;
    }

    public function currentUri($remove_querystring = false) {
        if ($remove_querystring)
            return $this->domainUri() . trim(ri_arr_first(explode('?', $this->server["REQUEST_URI"], 2)), " /");
        else
            return $this->domainUri() . trim($this->server["REQUEST_URI"], " /");
    }

    public function getClientIp() {
        $ip = FALSE;
        if (!empty($this->server["HTTP_CLIENT_IP"]))
            $ip = $this->server["HTTP_CLIENT_IP"];

        if (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            // Put the IP's into an array which we shall work with shortly.
            $ips = explode(", ", $this->server['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = false;
            }

            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        $ip = ($ip ? $ip : $this->server['REMOTE_ADDR']);
        if (in_array($ip, array("::1", "localhost"))) {
            $ip = "127.0.0.1";
        }

        return $ip;
    }

    public function method() {
        if (isset($this->post[self::METHOD_OVERRIDE])) {
            return $this->post[self::METHOD_OVERRIDE];
        } else {
            return isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : null;
        }
    }

    public function getContentType() {
        $contentType = isset($this->server["CONTENT_TYPE"]) ? $this->server["CONTENT_TYPE"] : isset($this->server["HTTP_CONTENT_TYPE"]) ? $this->server["HTTP_CONTENT_TYPE"] : null;
        if (!empty($contentType)) {
            $headerParts = preg_split('/\s*;\s*/', $contentType);
            $contentType = $headerParts[0];
        }
        return $contentType;
    }

    public function getPut() {
        if ($this->contentType == self::CONTENT_TYPE_FORM_URLENCODED) {
            $input = is_string($this->body) ? $this->body : '';
            if (function_exists('mb_parse_str')) {
                mb_parse_str($input, $output);
            } else {
                parse_str($input, $output);
            }
            $put = $output;
        } elseif ($this->method() == self::METHOD_PUT) {
            $put = $this->post->toArray();
        } else {
            $put = array();
        }
        return $put;
    }

    public function getBaseDir() {
        if (isset($this->server["SCRIPT_NAME"])) {
            $urlbase = explode("/", trim($this->server["SCRIPT_NAME"], "/ "));
            array_pop($urlbase); //script file, usually index.php
            $urlbase = implode("/", $urlbase);

            $baseDir = (!empty($urlbase) ? $urlbase . "/" : null);
        } else {
            $baseDir = "";
        }
        return $baseDir;
    }

    public function getResource() {
        $resource = '';
        if (isset($this->server["PATH_INFO"]) && (!empty($this->server["PATH_INFO"]))) {
            $resource = $this->server["PATH_INFO"];
        } else {
            if (isset($this->server["REQUEST_URI"])) {
                $currentUri = explode("?", $this->server["REQUEST_URI"], 2);
                $resource = substr(trim($currentUri[0], "/ "), strlen($this->baseDir));
            } elseif (isset($this->server["PHP_SELF"])) {
                $resource = $this->server["PHP_SELF"];
            } else {
                throw new RuntimeException('Unable to detect request URI');
            }
        }
        if (($this->baseUri() !== '') && (strpos($resource, $this->baseUri()) === 0)) {
            $resource = substr($resource, strlen($this->baseUri()));
        }
        $resource = trim($resource, '/ ');
        if (empty($resource))
            $resource = null;

        return $resource;
    }

    /**
     * Checks ["HTTPS"]
     * @return boolean 
     */
    public function isHttps() {
        return $this->server["HTTPS"] == "on";
    }

    public function isAjax() {
        return ($this->server["HTTP_X_REQUESTED_WITH"] == 'xmlhttprequest');
    }

    public function isUpload() {
        return (!ri_is_empty($this->files->toArray()));
    }

    public function isHead() {
        return $this->method() == self::METHOD_HEAD;
    }

    public function isGet() {
        return $this->method() == self::METHOD_GET;
    }

    public function isPost() {
        return $this->method() == self::METHOD_POST;
    }

    public function isPut() {
        return $this->method() == self::METHOD_PUT;
    }

    public function isDelete() {
        return $this->method() == self::METHOD_DELETE;
    }

    public function isOptions() {
        return $this->method() == self::METHOD_OPTIONS;
    }

    /**
     *
     * @param string $resource
     * @param string $method
     * @param array $get
     * @param array $post
     * @param array $server
     * @return Ri_Http_Request 
     */
    public static function build($resource, $method = "GET", $get = null, $post = null, $server = NULL) {
        $server = empty($server) ? $_SERVER : $server;
        $get = empty($get) ? $_GET : $get;
        $post = empty($post) ? $_POST : $post;

        $server["REQUEST_METHOD"] = $method;
        $server["PATH_INFO"] = $resource;
        $server["REQUEST_URI"] = $resource . '?' . http_build_query($get);
        $server["QUERY_STRING"] = http_build_query($get);

        $_GET = $get;
        $_POST = $post;

        return new Ri_Http_Request($server);
    }

}