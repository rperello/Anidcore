<?php

/**
 * Full Anidcore facade class
 * 
 */
class Ac extends Ac_System {

    /**
     *
     * @var array 
     */
    protected static $timers = array();

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
     * @param string $of Possible values: static (static assets), assets (theme assets), routerdir, action, controller, host, current, resource, dir, module (default)
     * @return string 
     */
    public static function url($of = "module") {
        switch ($of) {

            case "static":
            case "STATIC": {
                    return defined("AC_STATIC_ASSETS_URL") ? AC_STATIC_ASSETS_URL : self::request()->directoryUrl() . "content/assets/";
                }break;

            case "assets":
            case "ASSETS": {
                    return self::module()->assetsUrl();
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

            case "routerdir":
            case "ROUTERDIR": {
                    return self::router()->directoryUrl();
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
     * @param string $of Possible values are: view, static (static assets), assets (theme assets), app, system, content, modules, base, module (default)
     * @return string 
     */
    public static function path($of = "module") {
        switch ($of) {
            case "view":
            case "VIEW": {
                    return self::module()->viewsPath();
                }break;

            case "static":
            case "STATIC": {
                    return defined("AC_STATIC_ASSETS_PATH") ? AC_STATIC_ASSETS_PATH : AC_PATH_CONTENT . "assets" . _DS;
                }break;

            case "assets":
            case "ASSETS": {
                    return self::module()->assetsPath();
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
                    return self::module()->path();
                }break;
        }
    }

    public static function timerStart() {
        self::$timers[] = microtime(true);
    }

    /**
     *
     * @param int $start_time
     * @param boolean $detailed_result
     * @return int|string 
     */
    public static function timerStop($start_time = null, $detailed_result = true) {
        if ($start_time == null) {
            if (!empty(self::$timers)) {
                $start_time = array_pop(self::$timers);
            }else
                return 0;
        }

        $end_time = round((microtime(true) - $start_time), 3);

        if ($detailed_result) {
            if ($end_time < 1) {
                $end_time_str = ($end_time * 1000) . "ms";
            }else
                $end_time_str = $end_time . "s";

            return $end_time_str;
        }else {
            return $end_time;
        }
    }

}