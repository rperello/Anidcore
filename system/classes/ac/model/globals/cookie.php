<?php

class Ac_Model_Globals_Cookie extends Ac_Model_Globals {

    public function __construct() {
        parent::__construct("_COOKIE");
    }

    public function __set($name, $value) {
        parent::__set($name);
        $this->set($name, $value);
    }

    public function __unset($name) {
        parent::__unset($name);
        $this->delete($name);
    }

    /**
     * Send a cookie
     * @param string $name <p>
     * The name of the cookie.
     * </p>
     * @param string $value [optional] <p>
     * The value of the cookie. This value is stored on the clients computer;
     * do not store sensitive information. Assuming the
     * <i>name</i> is 'cookiename', this
     * value is retrieved through $_COOKIE['cookiename']
     * </p>
     * @param int $expire [optional] <p>
     * The time the cookie expires. This is a Unix timestamp so is
     * in number of seconds since the epoch. In other words, you'll
     * most likely set this with the <b>time</b> function
     * plus the number of seconds before you want it to expire. Or
     * you might use <b>mktime</b>.
     * time()+60*60*24*30 will set the cookie to
     * expire in 30 days. If set to 0, or omitted, the cookie will expire at
     * the end of the session (when the browser closes).
     * </p>
     * <p>
     * <p>
     * You may notice the <i>expire</i> parameter takes on a
     * Unix timestamp, as opposed to the date format Wdy, DD-Mon-YYYY
     * HH:MM:SS GMT, this is because PHP does this conversion
     * internally.
     * </p>
     * </p>
     * @param string $path [optional] <p>
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available
     * within the entire <i>domain</i>. If set to
     * '/foo/', the cookie will only be available
     * within the /foo/ directory and all
     * sub-directories such as /foo/bar/ of
     * <i>domain</i>. The default value is the
     * current directory that the cookie is being set in.
     * </p>
     * @param string $domain [optional] <p>
     * The domain that the cookie is available to. To make the cookie
     * available on all subdomains of example.com (including example.com
     * itself) then you'd set it to '.example.com'.
     * Although some browsers will accept cookies without the initial
     * ., RFC 2109
     * requires it to be included. Setting the domain to
     * 'www.example.com' or
     * '.www.example.com' will make the cookie only
     * available in the www subdomain.
     * </p>
     * @param bool $secure [optional] <p>
     * Indicates that the cookie should only be transmitted over a
     * secure HTTPS connection from the client. When set to true, the
     * cookie will only be set if a secure connection exists.
     * On the server-side, it's on the programmer to send this
     * kind of cookie only on secure connection (e.g. with respect to
     * $_SERVER["HTTPS"]).
     * </p>
     * @param bool $httponly [optional] <p>
     * When true the cookie will be made accessible only through the HTTP
     * protocol. This means that the cookie won't be accessible by
     * scripting languages, such as JavaScript. It has been suggested that
     * this setting can effectively help to reduce identity theft through
     * XSS attacks (although it is not supported by all browsers), but that
     * claim is often disputed. Added in PHP 5.2.0.
     * true or false
     * </p>
     * @return bool If output exists prior to calling this function,
     * <b>setcookie</b> will fail and return false. If
     * <b>setcookie</b> successfully runs, it will return true.
     * This does not indicate whether the user accepted the cookie.
     */
    public function set($name, $value, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = null) {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Sends an expired cookie for delete it
     * @param string $name <p>
     * The name of the cookie.
     * </p>
     * @param string $path [optional] <p>
     * The path on the server in which the cookie will be available on.
     * If set to '/', the cookie will be available
     * within the entire <i>domain</i>. If set to
     * '/foo/', the cookie will only be available
     * within the /foo/ directory and all
     * sub-directories such as /foo/bar/ of
     * <i>domain</i>. The default value is the
     * current directory that the cookie is being set in.
     * </p>
     * @param string $domain [optional] <p>
     * The domain that the cookie is available to. To make the cookie
     * available on all subdomains of example.com (including example.com
     * itself) then you'd set it to '.example.com'.
     * Although some browsers will accept cookies without the initial
     * ., RFC 2109
     * requires it to be included. Setting the domain to
     * 'www.example.com' or
     * '.www.example.com' will make the cookie only
     * available in the www subdomain.
     * </p>
     * @param bool $secure [optional] <p>
     * Indicates that the cookie should only be transmitted over a
     * secure HTTPS connection from the client. When set to true, the
     * cookie will only be set if a secure connection exists.
     * On the server-side, it's on the programmer to send this
     * kind of cookie only on secure connection (e.g. with respect to
     * $_SERVER["HTTPS"]).
     * </p>
     * @param bool $httponly [optional] <p>
     * When true the cookie will be made accessible only through the HTTP
     * protocol. This means that the cookie won't be accessible by
     * scripting languages, such as JavaScript. It has been suggested that
     * this setting can effectively help to reduce identity theft through
     * XSS attacks (although it is not supported by all browsers), but that
     * claim is often disputed. Added in PHP 5.2.0.
     * true or false
     * </p>
     * @return bool If output exists prior to calling this function,
     * <b>setcookie</b> will fail and return false. If
     * <b>setcookie</b> successfully runs, it will return true.
     * This does not indicate whether the user accepted the cookie.
     */
    public function delete($name, $path = null, $domain = null, $secure = false, $httponly = null) {
        return setcookie($name, "", time() - 3600, $path, $domain, $secure, $httponly);
    }

}