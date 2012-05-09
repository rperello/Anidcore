<?php

/**
 * Full Anidcore facade class
 * 
 */
class Ac extends Ac_System {

    /**
     *
     * @return Ac_Model_Globals 
     */
    public static function get() {
        return self::request()->GET;
    }

    /**
     *
     * @return Ac_Model_Globals 
     */
    public static function post() {
        return self::request()->POST;
    }

    /**
     *
     * @return Ac_Model_Globals
     */
    public static function input() {
        return self::request()->INPUT;
    }

    /**
     *
     * @return Ac_Model_Globals_Cookie
     */
    public static function cookie() {
        return self::request()->COOKIE;
    }

    public static function redirect($url, $status = 302) {
        self::response()->redirect($url, $status);
        self::response()->send();
        exit();
    }

    /**
     * Returns the specified url
     * @param string $of Possible values: media, assets, virtual, action, controller, host, current, resource, dir, module (default)
     * @return string 
     */
    public static function url($of = "module") {
        switch ($of) {
            case "media":
            case "MEDIA": {
                    return self::module()->mediaUrl();
                }break;

            case "assets":
            case "ASSETS": {
                    return defined("AC_CONTENT_URL") ? AC_CONTENT_URL : self::request()->directoryUrl() . "content/assets/";
                }break;

            case "virtual":
            case "VIRTUAL": {
                    return self::router()->virtualBaseUrl;
                }break;

            case "action":
            case "ACTION": {
                    return self::router()->actionUrl();
                }break;

            case "controller":
            case "CONTROLLER": {
                    return self::router()->controllerUrl();
                }break;

            case "host":
            case "HOST": {
                    return self::request()->hostUrl();
                }break;

            case "current":
            case "CURRENT": {
                    return self::request()->url();
                }break;

            case "resource":
            case "RESOURCE": {
                    return self::request()->resourceUrl();
                }break;

            case "dir":
            case "DIR": {
                    return self::request()->directoryUrl();
                }break;

            case "module":
            case "MODULE":
            default: {
                    return self::module()->url();
                }break;
        }
    }

    /**
     * Returns the specified path (filesystem)
     * @param string $of Possible values are: view, assets, media, app, system, content, modules, base, module (default)
     * @return string 
     */
    public static function path($of = "module") {
        switch ($of) {
            case "view":
            case "VIEW": {
                    return self::module()->viewsPath();
                }break;

            case "assets":
            case "ASSETS": {
                    return AC_PATH_CONTENT . "assets" . _DS;
                }break;

            case "media":
            case "MEDIA": {
                    return self::module()->mediaPath();
                }break;

            case "app":
            case "APP": {
                    return AC_PATH_APP;
                }break;

            case "system":
            case "SYSTEM": {
                    return AC_PATH_SYSTEM;
                }break;

            case "content":
            case "CONTENT": {
                    return AC_PATH_CONTENT;
                }break;

            case "modules":
            case "MODULES": {
                    return AC_PATH_MODULES;
                }break;

            case "base":
            case "BASE": {
                    return AC_PATH;
                }break;

            case "module":
            case "MODULE":
            default: {
                    return self::module()->path;
                }break;
        }
    }

}