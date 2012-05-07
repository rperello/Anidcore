<?php

class Ri_Module extends Ri_Environment {

    public $name;
    public $path;
    protected $defaults;

    public function __construct($moduleName, $contextName) {
        parent::__construct($contextName);

        $this->name = $moduleName;
        $this->path = $this->isContext() ? RI_PATH_APP : RI_PATH_MODULES . $moduleName . _DS;

        if (!is_dir($this->path)) {
            $this->context()->log()->fatal($this->path, "Module not found at given path", __FILE__, __LINE__);
        }

        //default config
        if (is_readable($this->path . "defaults.php")) {
            $defaults = include $this->path . "defaults.php";
        } else {
            $defaults = array();
        }
        
        $this->defaults = $defaults;

        $this->context()->setConfig(null, array_merge($defaults, $this->context()->config(null, array(), $moduleName)), $moduleName);
        
        $this->context()->hookApply("ri.on.load_module", $this);
        $this->context()->hookApply("ri.on.load_module.".$moduleName, $this);
    }

    public function init() {
        if (is_readable($this->path . "functions.php")) {
            include_once $this->path . "functions.php";
        }
        if (is_readable($this->path . "init.php")) {
            include $this->path . "init.php";
        }
        $this->context()->hookApply("ri.on.init_module", $this);
        $this->context()->hookApply("ri.on.init_module.".$this->name, $this);
    }

    public function isContext() {
        return $this->name == $this->context()->name;
    }

}