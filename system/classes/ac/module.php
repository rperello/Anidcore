<?php

class Ac_Module{

    public $name;
    public $path;
    protected $defaults;

    public function __construct($moduleName) {

        $this->name = $moduleName;
        $this->path = $this->isMain() ? AC_PATH_APP : AC_PATH_MODULES . $moduleName . _DS;

        if (!is_dir($this->path)) {
            Ac::$logger->fatal($this->path, "Module not found at given path", __FILE__, __LINE__);
        }

        //default config
        if (is_readable($this->path . "defaults.php")) {
            $defaults = include $this->path . "defaults.php";
        } else {
            $defaults = array();
        }

        $this->defaults = $defaults;

        Ac::setConfig(null, array_merge($defaults, Ac::config(null, array(), $moduleName)), $moduleName);

        Ac::hookApply("ac.on.load_module", $this);
        Ac::hookApply("ac.on.load_module." . $moduleName, $this);
    }

    public function init() {
        if (is_readable($this->path . "functions.php")) {
            include_once $this->path . "functions.php";
        }
        if (is_readable($this->path . "init.php")) {
            include $this->path . "init.php";
        }
        Ac::hookApply("ac.on.init_module", $this);
        Ac::hookApply("ac.on.init_module." . $this->name, $this);
    }

    public function isMain() {
        return $this->name == "app";
    }

}