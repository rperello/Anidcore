<?php

class Ri_Http_Response {

    /**
     * @var int HTTP status code
     */
    protected $status;

    /**
     * @var array Key-value array of HTTP response headers
     */
    protected $headers;

    /**
     * @var string HTTP response body
     */
    protected $body;

    /**
     * @var int Length of HTTP response body
     */
    protected $length;

    /**
     * @var array HTTP response codes and messages
     */
    protected static $messages = array(
        //Informational 1xx
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        //Successful 2xx
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        //Redirection 3xx
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 (Unused)',
        307 => '307 Temporary Redirect',
        //Client Error 4xx
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        //Server Error 5xx
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported'
    );

    /**
     * Constructor
     * @param   string    $body       The HTTP response body
     * @param   int       $status     The HTTP response status
     * @param   array     $headers    The HTTP response headers
     */
    public function __construct($body = '', $status = 200, $headers = array()) {
        $this->headers = $headers;
        $this->status($status);
        $this->body($body);
    }

    /**
     * Get and set header
     * @param   string          $name   Header name
     * @param   string|null     $value  Header value
     * @return  string                  Header value
     */
    public function header($name = null, $value = null) {
        if (empty($name)) {
            return $this->headers;
        }
        if ($value !== null) {
            $this->headers[$name] = $value;
        }
        return $this->headers[$name];
    }

    /**
     * Get and set body
     * @param   string|null  $body   Content of HTTP response body
     * @return  string
     */
    public function body($body = null) {
        if (func_num_args() > 0) {
            $this->body = $body;
            $this->length(strlen($body));
        }
        return $this->body;
    }

    /**
     * Get and set length
     * @param   int|null     $length
     * @return  int
     */
    public function length($length = null) {
        if (func_num_args() > 0) {
            $this->length = (int) $length;
            $this->header("Content-Length", $this->length);
        }
        return $this->length;
    }

    /**
     * Get and set status
     * @param   int|null     $status
     * @param   string       $httpVersion
     * @return  int
     */
    public function status($status = null, $httpVersion = "1.1") {
        if (func_num_args() > 0) {
            $this->status = (int) $status;
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                $this->header("Status", $this->status);
            } else {
                $this->header("HTTP/" . $httpVersion, $this->status);
            }
        }
        return $this->status;
    }

    /**
     * Redirect
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param   string  $url        The redirect destination
     * @param   int     $status     The redirect HTTP status code
     */
    public function redirect($url, $status = 302) {
        $this->status($status);
        $this->header('Location', $url);
    }

    public function sendHeaders() {
        if (!headers_sent()) {
            foreach ($this->headers as $k => $v) {
                header($k . ': ' . $v, true);
            }
            return true;
        }
        return false;
    }

    public function prepare($status = 200, $contentType = "text/html") {
        $this->status($status);
        if ($this->isEmpty()) {
            if (isset($this->headers['Content-Type']))
                unset($this->headers['Content-Type']);
            if (isset($this->headers['Content-Length']))
                unset($this->headers['Content-Length']);
        }else {
            $this->header("Content-Type", $contentType);
        }

        return array("body" => $this->body, "status" => $this->status, "headers" => $this->headers);
    }

    public function send() {
        $this->sendHeaders();
        echo $this->body;
    }

    public function __toString() {
        return $this->body;
    }

    /**
     * Helpers: Empty?
     * @return bool
     */
    public function isEmpty() {
        return in_array($this->status, array(201, 204, 304));
    }

    /**
     * Helpers: Informational?
     * @return bool
     */
    public function isInformational() {
        return $this->status >= 100 && $this->status < 200;
    }

    /**
     * Helpers: OK?
     * @return bool
     */
    public function isOk() {
        return $this->status === 200;
    }

    /**
     * Helpers: Successful?
     * @return bool
     */
    public function isSuccessful() {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Helpers: Redirect?
     * @return bool
     */
    public function isRedirect() {
        return in_array($this->status, array(301, 302, 303, 307));
    }

    /**
     * Helpers: Redirection?
     * @return bool
     */
    public function isRedirection() {
        return $this->status >= 300 && $this->status < 400;
    }

    /**
     * Helpers: Forbidden?
     * @return bool
     */
    public function isForbidden() {
        return $this->status === 403;
    }

    /**
     * Helpers: Not Found?
     * @return bool
     */
    public function isNotFound() {
        return $this->status === 404;
    }

    /**
     * Helpers: Client error?
     * @return bool
     */
    public function isClientError() {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Helpers: Server Error?
     * @return bool
     */
    public function isServerError() {
        return $this->status >= 500 && $this->status < 600;
    }

}