<?php

/**
 * Rino Framework main facade class
 */
class Ri {
    /**
     * Rino Framework Version 
     */

    const VERSION = "0.0.2";
    const CHARS_HEXADECIMAL = "abcdef0123456789";
    const CHARS_ALPHANUMERIC = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    const CHARS_SYMBOLS = "{}()[]<>!?|@#%&/=^*;,:.-_+";

    /**
     * Alias for Ri_Application::getInstance
     * @param string $name
     * @return Ri_Application
     * @throws RuntimeException 
     */
    public static function app($name = null) {
        if (empty($name))
            $name = Ri_Application::DEFAULT_INSTANCE_NAME;
        return Ri_Application::getInstance($name);
    }

}