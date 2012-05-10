<?php

class Ac_Module {

    public $name;
    public $path;
    protected $defaults;
    protected $hasAutoload = false;
    protected $hasViews = false;
    protected $isMultiTheme = false;
    protected $currentTheme = null;

    protected function __construct($moduleName, $defaults = array()) {

        $this->name = $moduleName;
        $this->path = $this->isMain() ? AC_PATH_APP : AC_PATH_MODULES . $moduleName . _DS;

        if (!is_dir($this->path)) {
            Ac::log()->fatal($this->path, "Module not found at given path", __FILE__, __LINE__);
        }

        $this->defaults = $defaults;

        $this->setConfig(null, array_merge($defaults, $this->config(null, array())));

        if (is_dir($this->path . "classes")) {
            $files = ac_dir_files($this->path . "classes");
            $this->hasAutoload = count($files) > 1;
        }

        if (is_dir($this->path . "views")) {
            $this->hasViews = true;
        }

        $theme = $this->config("theme", null);
        if (!empty($theme) && is_dir($this->viewsPath() . $theme)) {
            $this->isMultiTheme = true;
            $this->currentTheme = $theme;
        }
    }

    /**
     *
     * @param string $moduleName
     * @param array $defaults
     * @return Ac_Module
     */
    public static function factory($moduleName, $defaults = array()) {
        $path = ($moduleName == "app") ? AC_PATH_APP : AC_PATH_MODULES . $moduleName . _DS;

        if (!is_dir($path)) {
            Ac::log()->fatal($path, "Module not found at given path", __FILE__, __LINE__);
        }

        $moduleClass = 'Module_' . ucfirst($moduleName);
        $classFound = false;

        //Try to load the moduleClass using the HMVC power
        if (Ac::loader()->autoload($moduleClass)) {
            $classFound = true;
        } else {
            //Lookup under the module /classes dir
            $moduleFile = $path . 'classes' . _DS . 'module' . _DS . strtolower($moduleName) . '.php';
            if (is_readable($moduleFile)) {
                include_once $moduleFile;
                if (class_exists($moduleClass, false)) {
                    $classFound = true;
                }
            }
        }

        if ($classFound) {
            return new $moduleClass($defaults);
        }else
            return new Ac_Module($moduleName, $defaults);
    }

    public function init() {
        if (is_readable($this->path . "init.php")) {
            include $this->path . "init.php";
        }
    }

    public function name() {
        return $this->name;
    }

    public function path() {
        return $this->path;
    }

    public function isMain() {
        return $this->name == "app";
    }

    public function hasAutoload() {
        return $this->hasAutoload;
    }

    public function hasViews() {
        return $this->hasViews;
    }

    public function isMultiTheme() {
        return $this->isMultiTheme;
    }

    public function theme($newTheme = null) {
        if (!empty($newTheme))
            $this->currentTheme = $newTheme;
        return $this->currentTheme;
    }

    public function url() {
        if ($this->name == "app") {
            return Ac::url("virtual");
        } else {
            return Ac::url("virtual") . $this->config('default_controller', $this->name) . '/';
        }
    }

    public function viewsPath() {
        if ($this->isMultiTheme) {
            return $this->path . "views" . _DS . $this->currentTheme . _DS;
        } else {
            return $this->path . "views" . _DS;
        }
    }

    public function mediaPath() {
        return $this->viewsPath() . "media" . _DS;
    }

    public function mediaUrl() {
        $url = Ac::url("base");
        if (!$this->isMain()) {
            $url .= $this->name . "/";
        }

        if ($this->isMultiTheme) {
            return $url . "themes/" . $this->currentTheme . "/media/";
        } else {
            return $url . "media/";
        }
    }

    public function config($name = null, $default = false) {
        return Ac::config($name, $default, $this->name);
    }

    public function setConfig($name, $value) {
        return Ac::setConfig($name, $value, $this->name);
    }

}