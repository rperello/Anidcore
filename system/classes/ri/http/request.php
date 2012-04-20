<?php

class Ri_Http_Request{
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_OVERRIDE = '_METHOD';
    
    const CONTENT_TYPE_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    
    /**
     * Original Super Globals
     * @var array
     */
    protected static $originals;
    
    /**
     *
     * @var Ri_Http_Request 
     */
    protected static $instance;
    
    /**
     * $_SERVER variables
     * @var array 
     */
    protected $server;
    
    /**
     * Relative script path (from document root)
     * @var type 
     */
    public $scriptName;
    public $pathInfo;
    public $phpSelf;
    
    public $requestUri;
    public $queryString;
    public $httpXRequestedWith;
    public $referer;
    public $userAgent;
    
    /**
     * Language list formatted like 'es-es,es,en-us,en'
     * @var string 
     */
    public $acceptLanguage;
    public $clientIp;
    
    public $body;
    public $get;
    public $post;
    public $request;
    public $argv;
    public $argc;
    public $env;
    public $put;
    public $files;
    public $cookie;
    
    public $authUser;
    public $authPassword;
    public $authDigest;
    public $authType;
    
    public $contentType;
    public $method;
    public $serverProtocol;
    /**
     * Server protocol
     * @var string 
     */
    public $scheme;
    public $https;
    
    /**
     * TCP/IP Server Port
     * @var int 
     */
    public $port;
    
    /**
     * serverName alias
     * @var string 
     */
    public $host;
    public $serverName;
    
    /**
     * Full domain URI
     * @var string 
     */
    public $hostUri;
    
    /**
     * Base directory
     * @var string 
     */
    public $baseDir;
    
    /**
     * Base directory URI
     * @var string 
     */
    public $baseUri;
    
    /**
     * Requested resource path
     * @var string 
     */
    public $resource;
    
    /**
     * Current full URI (without query string)
     * @var string 
     */
    public $currentUri;
    
    public function __construct($server=NULL) {
        if(!isset(self::$originals)){
            self::$originals = array(
                "SERVER"=>$_SERVER,
                "GET"=>$_GET,
                "POST"=>$_POST,
                "REQUEST"=>$_REQUEST,
                "ENV"=>$_ENV,
                "FILES"=>$_FILES,
                "COOKIE"=>$_COOKIE
            );
        }
        
        $this->server = empty($server) ? $_SERVER : $server;
            
        $this->serverProtocol = ri_arr_value($this->server, "SERVER_PROTOCOL", "http");
        $this->https = ri_arr_value($this->server, "HTTPS", "off");
        $this->serverName = ri_arr_value($this->server, "SERVER_NAME", "localhost");
        
        $this->scriptName = ri_arr_value($this->server, "SCRIPT_NAME", NULL);
        $this->pathInfo = ri_arr_value($this->server, "PATH_INFO", NULL);
        $this->phpSelf = ri_arr_value($this->server, "PHP_SELF", NULL);
        
        $this->requestUri = ri_arr_value($this->server, "REQUEST_URI", NULL);
        $this->queryString = ri_arr_value($this->server, "QUERY_STRING", NULL);
        $this->port = @parse_url($this->requestUri, PHP_URL_PORT);
        $this->port = !empty($this->port) ? $this->port : 80;

        $this->authUser = ri_arr_value($this->server, "PHP_AUTH_USER", NULL);
        $this->authPassword = ri_arr_value($this->server, "PHP_AUTH_PW", NULL);
        $this->authDigest = ri_arr_value($this->server, "PHP_AUTH_DIGEST", NULL);
        $this->authType = ri_arr_value($this->server, "AUTH_TYPE", NULL);

        $this->referer = ri_arr_value($this->server, "HTTP_REFERER", NULL);
        $this->userAgent = ri_arr_value($this->server, "HTTP_USER_AGENT", NULL);
        $this->acceptLanguage = preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", ri_arr_value($this->server, "HTTP_ACCEPT_LANGUAGE", NULL));
        
        $this->clientIp = $this->getClientIp();

        $this->body = @file_get_contents('php://input');
        $this->get = $_GET;
        $this->post = $_POST;
        $this->argv = ri_arr_value($this->server, "argv", array());
        $this->argc = ri_arr_value($this->server, "argc", 0);
        $this->env = $_ENV;
        
        $this->files = $_FILES;
        $this->cookie = $_COOKIE;

        $this->contentType = $this->getContentType();
        $this->method=$this->getMethod();
        $this->httpXRequestedWith=strtolower(ri_arr_value($this->server, "HTTP_X_REQUESTED_WITH", NULL));

        $this->put = $this->getPut();
        
        $this->request = array_merge($this->cookie, $this->put, $this->post, $this->get);
        
        //scheme
        if ($this->https=="on"){
            $this->scheme = "https";
        }else {
            $this->scheme = ri_arr_first(explode("/",strtolower($this->serverProtocol), 2));
        }
        
        $this->host = $this->serverName;
        $this->hostUri = $this->scheme . "://" . $this->host . (($this->port == 80) ? "" : ":" . $this->port) . "/";
        
        $this->baseDir = $this->getBaseDir();
        $this->baseUri = $this->hostUri . $this->baseDir;
        
        $currentUri = explode("?", $this->requestUri, 2);
        $this->currentUri = $this->hostUri . trim($currentUri[0], " /");
        
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

    public function superGlobalReplace() {
        $_SERVER = $this->server;
        $_GET = $this->get;
        $_POST = $this->post;
        $_REQUEST = $this->request;
        $_ENV = $this->env;
        $_FILES = $this->files;
        $_COOKIE = $this->cookie;
    }

    public static function superGlobals() {
        return self::$originals;
    }

    public static function superGlobalRestore() {
        if (isset(self::$originals)) {
            $_SERVER = self::$originals["SERVER"];
            $_GET = self::$originals["GET"];
            $_POST = self::$originals["POST"];
            $_REQUEST = self::$originals["REQUEST"];
            $_ENV = self::$originals["ENV"];
            $_FILES = self::$originals["FILES"];
            $_COOKIE = self::$originals["COOKIE"];
        }
    }

    protected function getClientIp() {
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

    protected function getMethod() {
        if (isset($this->post[self::METHOD_OVERRIDE])) {
            return $this->post[self::METHOD_OVERRIDE];
        } else {
            return isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : NULL;
        }
    }

    protected function getContentType() {
        $contentType = isset($this->server["CONTENT_TYPE"]) ? $this->server["CONTENT_TYPE"] : isset($this->server["HTTP_CONTENT_TYPE"]) ? $this->server["HTTP_CONTENT_TYPE"] : NULL;
        if (!empty($contentType)) {
            $headerParts = preg_split('/\s*;\s*/', $contentType);
            $contentType = $headerParts[0];
        }
        return $contentType;
    }

    protected function getPut() {
        if ($this->contentType == self::CONTENT_TYPE_FORM_URLENCODED) {
            $input = is_string($this->body) ? $this->body : '';
            if (function_exists('mb_parse_str')) {
                mb_parse_str($input, $output);
            } else {
                parse_str($input, $output);
            }
            $put = $output;
        } elseif ($this->method == self::METHOD_PUT) {
            $put = $this->post;
        } else {
            $put = array();
        }
        return $put;
    }

    protected function getBaseDir() {
        if (isset($this->scriptName)) {
            $urlbase = explode("/", trim($this->scriptName, "/ "));
            array_pop($urlbase); //script file, usually index.php
            $urlbase = implode("/", $urlbase);

            $baseDir = (!empty($urlbase) ? $urlbase . "/" : NULL);
        } else {
            $baseDir = "";
        }
        return $baseDir;
    }

    protected function getResource() {
        $resource = '';
        if (isset($this->server["PATH_INFO"]) && (!empty($this->pathInfo))) {
            $resource = $this->pathInfo;
        } else {
            if (isset($this->server["REQUEST_URI"])) {
                $currentUri = explode("?", $this->requestUri, 2);
                $resource = substr(trim($currentUri[0], "/ "), strlen($this->baseDir));
            } elseif (isset($this->server["PHP_SELF"])) {
                $resource = $this->phpSelf;
            } else {
                throw new RuntimeException('Unable to detect request URI');
            }
        }
        if (($this->baseUri !== '') && (strpos($resource, $this->baseUri) === 0)) {
            $resource = substr($resource, strlen($this->baseUri));
        }
        $resource = trim($resource, '/ ');
        if (empty($resource))
            $resource = NULL;

        return $resource;
    }

    public function get($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->get;
        } else {
            return ri_arr_value($this->get, $key, $default, $filter);
        }
    }

    public function post($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->post;
        } else {
            return ri_arr_value($this->post, $key, $default, $filter);
        }
    }

    public function request($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->request;
        } else {
            return ri_arr_value($this->request, $key, $default, $filter);
        }
    }

    public function arg($index = NULL, $default = false, $filter = NULL) {
        if ($index === NULL) {
            return $this->arg;
        } else {
            return ri_arr_value($this->arg, $index, $default, $filter);
        }
    }

    public function env($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->env;
        } else {
            return ri_arr_value($this->env, $key, $default, $filter);
        }
    }

    public function put($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->put;
        } else {
            return ri_arr_value($this->put, $key, $default, $filter);
        }
    }

    public function files() {
        return $this->files;
    }

    public function cookie($key = NULL, $default = false, $filter = NULL) {
        if ($key === NULL) {
            return $this->cookie;
        } else {
            return ri_arr_value($this->cookie, $key, $default, $filter);
        }
    }

    public function isAjax() {
        return ($this->httpXRequestedWith == 'xmlhttprequest');
    }

    public function isUpload() {
        return (!empty($this->files));
    }

    public function isHead() {
        return $this->method == self::METHOD_HEAD;
    }

    public function isGet() {
        return $this->method == self::METHOD_GET;
    }

    public function isPost() {
        return $this->method == self::METHOD_POST;
    }

    public function isPut() {
        return $this->method == self::METHOD_PUT;
    }

    public function isDelete() {
        return $this->method == self::METHOD_DELETE;
    }

    public function isOptions() {
        return $this->method == self::METHOD_OPTIONS;
    }
}