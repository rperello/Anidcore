<?php

/**
 * Exposes a Ri_Context to the extended classes 
 */
class Ri_Environment {

    /**
     * Ri_Context instance name
     * @var string
     */
    protected $contextName;

    public function __construct($contextName) {
        $this->contextName = $contextName;
    }

    /**
     * Ri_Context instance name
     * @return Ri_Context 
     */
    public function context() {
        return Ri_Context::getInstance($this->contextName);
    }

}