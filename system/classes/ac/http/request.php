<?php

/**
 * @property-read string $domain
 * @property-read boolean $isCli
 * @property-read boolean $isAjax
 * @property-read boolean $isHttps
 * @property-read boolean $isUpload
 * @property-read string $serverProtocol
 * @property-read string $uriSchema
 * @property-read string $serverPort
 * @property-read string $requestMethod
 * @property-read string $clientIp
 * @property-read string $contentType
 * @property-read string $languages
 * @property-read string $domainUri
 * @property-read string $baseUri
 * @property-read string $resource
 * @property-read string $resourceUri
 * @property-read string $requestUri
 * @property-read string $virtualUri
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
     * Uploads a representation of the specified resource.
     */
    const METHOD_OVERRIDE = '_METHOD';

    public function __construct($resource = false, $requestMethod = false) {
        $this->server = Ac::server();

        $this->domain = $this->server->__("SERVER_NAME", "localhost");

        $this->isCli = (PHP_SAPI == "cli");

        $this->isAjax = (strtolower($this->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        $this->isHttps = ($this->server['HTTPS'] == 'on');

        $this->isUpload = !empty($_FILES);

        $this->findProtocolSchema();

        $this->findServerPort();

        if ($requestMethod === false)
            $this->findRequestMethod();
        else
            $this->requestMethod = $requestMethod;

        $this->findClientIp();

        $this->findContentType();

        $this->languages = preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", $this->server["HTTP_ACCEPT_LANGUAGE"]);

        $this->domainUri = $this->uriSchema . "://" . $this->domain . (($this->serverPort == 80) ? "" : ":" . $this->serverPort) . "/";
        $this->baseUri = $this->domainUri . AC_BASEDIR;

        if ($resource === false)
            $this->findResource();
        else
            $this->resource = trim(ac_arr_first(explode('?', $resource, 2)), " /");

        $this->resourceUri = $this->baseUri . trim($this->resource, " /");
        $this->requestUri = !empty($_GET) ? $this->resourceUri . '?' . http_build_query($_GET) : $this->resourceUri;

        $this->findPutDelete();
    }
    
    public function setResource($resource){
        $this->resource = trim($resource, " /");
        $this->resourceUri = $this->domainUri . $this->resource;
        $this->requestUri = !empty($_GET) ? $this->resourceUri . '?' . http_build_query($_GET) : $this->resourceUri;
    }

    protected function findServerPort() {
        $REQUEST_URI = explode("?", $this->server["REQUEST_URI"], 2);
        $port = parse_url($REQUEST_URI[0], PHP_URL_PORT);
        $this->serverPort = !empty($port) ? $port : 80;
    }

    protected function findProtocolSchema() {
        $this->serverProtocol = explode('/', $this->server->__("SERVER_PROTOCOL", "HTTP/1.1"), 2);
        if ($this->isHttps && ($this->serverProtocol[0] == "HTTP")) {
            $this->uriSchema = "https";
        } else {
            $this->uriSchema = strtolower($this->serverProtocol[0]);
        }
        $this->serverProtocol = implode('/', $this->serverProtocol);

        return $this->uriSchema;
    }

    protected function findRequestMethod() {
        if (isset($_POST[self::METHOD_OVERRIDE])) {
            $this->requestMethod = $_POST[self::METHOD_OVERRIDE];
        } else {
            $this->requestMethod = isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : self::METHOD_GET;
        }
    }

    protected function findClientIp() {
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

        $this->clientIP = $ip;
        return $this->clientIP;
    }

    protected function findContentType() {
        $contentType = isset($this->server["CONTENT_TYPE"]) ? $this->server["CONTENT_TYPE"] : isset($this->server["HTTP_CONTENT_TYPE"]) ? $this->server["HTTP_CONTENT_TYPE"] : null;
        if (!empty($contentType)) {
            $headerParts = preg_split('/\s*;\s*/', $contentType);
            $contentType = $headerParts[0];
        }
        $this->contentType = $contentType;
        return $contentType;
    }

    protected function findPutDelete() {
        $customMethod = $this->requestMethod;
        if (isset($_ENV["_{$customMethod}"]))
            unset($_ENV["_{$customMethod}"]);
        if (in_array($this->server['REQUEST_METHOD'], array(self::METHOD_PUT, self::METHOD_DELETE))) {
            $body = @file_get_contents('php://input');
            $input = is_string($body) ? $body : '';
            if (function_exists('mb_parse_str')) {
                mb_parse_str($input, $output);
            } else {
                parse_str($input, $output);
            }
            $_ENV["_{$customMethod}"] = $output;
        } elseif (in_array($customMethod, array(self::METHOD_PUT, self::METHOD_DELETE))) {
            $_ENV["_{$customMethod}"] = $_POST;
        }

        if (!isset($_ENV["_PUT"]))
            $_ENV["_PUT"] = array();
        if (!isset($_ENV["_DELETE"]))
            $_ENV["_DELETE"] = array();
    }

    protected function findResource() {
        $resource = '';
        if (isset($this->server["PATH_INFO"]) && (!empty($this->server["PATH_INFO"]))) {
            $resource = $this->server["PATH_INFO"];
        } else {
            if (isset($this->server["REQUEST_URI"])) {
                $currentUri = explode("?", $this->server["REQUEST_URI"], 2);
                $resource = substr(trim($currentUri[0], "/ "), strlen(AC_BASEDIR));
            } elseif (isset($this->server["PHP_SELF"])) {
                $resource = $this->server["PHP_SELF"];
            } else {
                throw new RuntimeException('Unable to detect request URI');
            }
        }
        if (($this->baseUri !== '') && (strpos($resource, $this->baseUri) === 0)) {
            $resource = substr($resource, strlen($this->baseUri));
        }
        $resource = trim($resource, '/ ');
        if (empty($resource))
            $resource = null;

        $this->resource = $resource;
        return $this->resource;
    }

}