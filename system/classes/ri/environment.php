<?php

class Ri_Environment {

    /**
     * Ri_Application instance name
     * @var string
     */
    protected $appName;

    public function __construct($appName) {
        $this->appName = $appName;
    }

    /**
     * Ri_Application instance name
     * @return Ri_Application 
     */
    public function app() {
        return Ri::app($this->appName);
    }

}