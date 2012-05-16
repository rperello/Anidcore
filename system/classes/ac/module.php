<?php

class Ac_Module {

    protected $name;
    protected $path;
    protected $defaults;
    protected $hasAutoload = false;
    protected $hasTemplates = false;
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
            $this->hasAutoload = (count($files) > 1);
        }

        if (is_dir($this->path . "templates")) {
            $this->hasTemplates = true;
        }

        if (is_readable($this->path . "functions.php")) {
            include_once $this->path . "functions.php";
        }

        $theme = $this->config("theme", null);
        if (!empty($theme) && is_dir($this->templatesPath() . $theme)) {
            $this->isMultiTheme = true;
            $this->currentTheme = $theme;
        }
        Ac::trigger('AC_Module_on_create', $this);
        Ac::trigger('AC_Module_on_create_' . $this->name, $this);
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

    public function hasTemplates() {
        return $this->hasTemplates;
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
            return Ac::url("routerdir");
        } else {
            return Ac::url("routerdir") . $this->config('default_controller', $this->name) . '/';
        }
    }

    public function templatesPath() {
        if ($this->isMultiTheme) {
            return $this->path . "templates" . _DS . $this->currentTheme . _DS;
        } else {
            return $this->path . "templates" . _DS;
        }
    }

    public function assetsPath() {
        return $this->templatesPath() . "assets" . _DS;
    }

    public function assetsUrl() {
        $url = Ac::url("dir");
        if (!$this->isMain()) {
            $url .= $this->name . "/";
        }

        if ($this->isMultiTheme) {
            return $url . "themes/" . $this->currentTheme . "/assets/";
        } else {
            return $url . "assets/";
        }
    }

    public function config($name = null, $default = false) {
        return Ac::config($name, $default, $this->name);
    }

    public function setConfig($name, $value) {
        return Ac::setConfig($name, $value, $this->name);
    }

}