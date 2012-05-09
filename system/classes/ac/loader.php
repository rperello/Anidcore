<?php

require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "singleton.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac" . _DS . "system.php";
require_once AC_PATH_SYSTEM . "classes" . _DS . "ac.php";

class Ac_Loader extends Ac_Singleton{
    /**
     *
     * @var array 
     */
    public $config;
    public $modules;
    
    protected function __construct(){
        $this->config = Ac::hookApply(Ac::HOOK_BEFORE_INIT, array_merge(self::defaults(), include AC_PATH_APP . "config.php"));
        spl_autoload_register(array($this, '__autoload'));
    }
    
    public function __autoload($class_name){
        
    }
}