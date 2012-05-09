<?php

/**
 * HTTP Request
 */
class Ac_Http_Request {
    /**
     * Asks for the response identical to the one that would correspond to a GET request,
     * but without the response body. This is useful for retrieving meta-information written
     * in response headers, without having to transport the entire content.
     */

    const METHOD_HEAD = 'HEAD';

    /**
     * Requests a representation of the specified resource. Requests using GET should only
     * retrieve data and should have no other effect.
     * 
     * Some methods (for example, HEAD, GET, OPTIONS and TRACE) are defined as safe, which
     * means they are intended only for information retrieval and should not change the state
     * of the server. In other words, they should not have side effects, beyond relatively
     * harmless effects such as logging, caching, the serving of banner advertisements or
     * incrementing a web counter.
     */
    const METHOD_GET = 'GET';

    /**
     * Submits data to be processed (e.g., from an HTML form) to the identified resource.
     * The data is included in the body of the request. This may result in the creation of
     * a new resource or the updates of existing resources or both.
     * */
    const METHOD_POST = 'POST';

    /**
     * Uploads a representation of the specified resource.
     */
    const METHOD_PUT = 'PUT';

    /**
     * Deletes the specified resource.
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * Returns the HTTP methods that the server supports for specified URL. This can be
     * used to check the functionality of a web server by requesting '*' instead of a specific resource.
     */
    const METHOD_OPTIONS = 'OPTIONS';

    /**
     *
     * @var Ac_Context 
     */
    protected $context;

    /**
     *
     * @var Ac_Global 
     */
    public $GET;

    /**
     *
     * @var Ac_Global 
     */
    public $POST;

    /**
     *
     * @var Ac_Array 
     */
    public $PUT;

    /**
     *
     * @var Ac_Array 
     */
    public $DELETE;

    /**
     *
     * @var Ac_Global_Cookie 
     */
    public $COOKIE;

    public function __construct($context = null) {
        if (empty($context))
            $context = Ac_Context::getInstance();
        $this->context = $context;

        $input = $this->parsedInput();

        $this->GET = new Ac_Global("_GET");
        $this->POST = new Ac_Global("POST");
        $this->PUT = ($context->method == self::METHOD_PUT) ? $input : new Ac_Array();
        $this->DELETE = ($context->method == self::METHOD_DELETE) ? $input : new Ac_Array();

        $this->COOKIE = new Ac_Global_Cookie();
    }

    protected function parsedInput() {
        if (in_array($this->context->method, array(self::METHOD_PUT, self::METHOD_DELETE))) {
            $body = $this->rawInput();
            $input = is_string($body) ? $body : '';
            if (function_exists('mb_parse_str')) {
                mb_parse_str($input, $output);
            } else {
                parse_str($input, $output);
            }
            $result = $output;
        } else {
            $result = $_POST;
        }

        $output = new Ac_Array();
        $output->import($result);

        return $output;
    }

    public function host() {
        return $this->context->host;
    }

    /**
     * Protocol scheme
     * @return string 
     */
    public function scheme() {
        return $this->context->scheme;
    }

    /**
     * Protocol version
     * @return string 
     */
    public function version() {
        return $this->context->version;
    }

    public function port() {
        return $this->context->port;
    }

    public function method() {
        return $this->context->method;
    }

    public function directory() {
        return $this->context->directory;
    }

    public function resource() {
        return $this->context->resource;
    }

    public function resourceFormat() {
        return $this->context->resource_format;
    }

    public function queryString() {
        return $this->context->query_string;
    }

    public function rawInput() {
        return $this->context->raw_input;
    }

    public function userAgent() {
        return $this->context->user_agent;
    }

    /**
     * Client keyboard available languages
     * @return string (comma-separated and lowercased langs) 
     */
    public function languages() {
        return $this->context->languages;
    }

    public function clientIp() {
        return $this->context->client_ip;
    }

    public function isCli() {
        return $this->context->is_cli;
    }

    public function isAjax() {
        return $this->context->is_ajax;
    }

    public function isHttps() {
        return $this->context->scheme == "https";
    }

    public function isUpload() {
        return !empty($_FILES);
    }

    public function isGet() {
        return $this->context->method == self::METHOD_GET;
    }

    public function isPost() {
        return $this->context->method == self::METHOD_POST;
    }

    public function isPut() {
        return $this->context->method == self::METHOD_PUT;
    }

    public function isDelete() {
        return $this->context->method == self::METHOD_DELETE;
    }

    public function isOptions() {
        return $this->context->method == self::METHOD_OPTIONS;
    }

    public function hostUrl() {
        return $this->context->host_url;
    }

    public function directoryUrl() {
        return $this->context->host_url . $this->context->directory;
    }

    public function resourceUrl() {
        return $this->directoryUrl() . $this->context->resource;
    }

    public function url() {
        return !empty($this->context->query_string) ? $this->resourceUrl() . '?' . $this->context->query_string : $this->resourceUrl();
    }

}