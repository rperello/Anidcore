<?php

/**
 * Facade class to access the main features of the 'admin' module 
 */
class Admin_App extends Ac {

    protected $current_language = null;
    protected $current_document = null;

    /**
     *
     * @return Module_App
     */
    public static function main() {
        return self::module("app");
    }

    /**
     *
     * @return Module_Admin 
     */
    public static function admin() {
        return self::module("admin");
    }

    /**
     *
     * @param string|int $code Language code or id. If ommited, current language
     * will be returned.
     * @return R_Language
     */
    public static function language($code = null) {
        
    }

    /**
     *
     * @param int $id Document id. If ommited, current document will be returned.
     * @return R_Document
     */
    public static function document($id = null) {
        
    }

    /**
     *
     * @return R_User Current authorized user
     */
    public static function user() {
        
    }

    /**
     * Returns the module logo url
     * @param string $moduleName Module to fech the logo from
     * (the logo must be under the assets/img directory of the module)
     * 
     * @param string $name Logo file name (without path or extension)
     * @return string|false The logo url. False if not logo can be found.
     */
    public static function logo($moduleName = null, $name = "logo") {
        if (empty($moduleName)) {
            $moduleName = static::module()->name();
        }
        if (static::config("assets.{$name}")) {
            return static::module($moduleName)->assetsUrl() . "img/" . static::config("assets.{$name}");
        }

        $filename = static::module($moduleName)->assetsPath() . "img/" . $name;
        if (is_readable($filename . ".png")) {
            $ext = ".png";
        } elseif (is_readable($filename . ".jpg")) {
            $ext = ".jpg";
        } elseif (is_readable($filename . ".gif")) {
            $ext = ".gif";
        } else {
            return false;
        }

        static::setConfig("assets.{$name}", $name . $ext);
        return static::module($moduleName)->assetsUrl() . "img/" . $name . $ext;
    }

}