<?php

/**
 * This class logs messages into the session that provide
 * relevant information about the state of the current request.
 * 
 * This is useful for example: provide an error notification system for
 * form validation or send a message to the next page load
 * (before sending a redirection header, for example).
 */
class Ac_Log_Flash extends Ac_Log_File {
    /**
     * Session Namespace 
     */

    const SESSION_NS = "AC_LOG_FLASH";

    public function __construct() {
        if (!isset($_SESSION[self::SESSION_NS])) {
            $_SESSION[self::SESSION_NS] = array();
        }
    }

    public function write($data, $label = null, $options = array()) {
        // we can use $options to define the 'fn' name directly
        if (is_string($options)) {
            $fn = $options;
            $options = array();
        } else {
            $fn = null;
        }

        $options = array_merge(array(
            "func" => "log",
            "append" => "",
            "use_timestamp" => false,
                ), $options);

        $message = $label . print_r($data, true) . $options["append"];
        if (empty($fn))
            $fn = $options["func"];

        if ($options["use_timestamp"])
            $message = "[" . date("d-M-Y H:i:s") . "] " . $message;

        if (!isset($_SESSION[self::SESSION_NS][$fn])) {
            $_SESSION[self::SESSION_NS][$fn] = array();
        }

        $_SESSION[self::SESSION_NS][$fn][] = $message;

        return count($_SESSION[self::SESSION_NS][$fn]);
    }

    public function hasMessages($type = null) {
        return count($this->getMessages($type)) > 0;
    }

    public function getMessages($type = null) {
        if ($type == null)
            return $_SESSION[self::SESSION_NS];
        if (!isset($_SESSION[self::SESSION_NS][$type]))
            return array();
        else
            return $_SESSION[self::SESSION_NS][$type];
    }

    public function flushMessages($type = null) {
        $messages = $this->getMessages($type);
        $this->clearMessages($type);
        return $messages;
    }

    public function clearMessages($type) {
        if ($type == null)
            $_SESSION[self::SESSION_NS] = array();
        elseif (isset($_SESSION[self::SESSION_NS][$type]))
            $_SESSION[self::SESSION_NS][$type] = array();
    }

}