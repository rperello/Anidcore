<?php

class Ri{
    /**
     * @var array[Ri]
     */
    protected static $apps = array();
    protected static $defaults;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Ri_Http_Request
     */
    protected $request;

    /**
     * @var Ri_Http_Response
     */
    protected $response;

    /**
     * @var Ri_Router
     */
    protected $router;

    /**
     * @var Ri_View
     */
    protected $view;

    /**
     * @var Ri_Log
     */
    protected $log;

    /**
     * @var array Key-value array of application settings
     */
    protected $settings;
    
    /**
     * Registry variables
     * @var array 
     */
    protected $vars;
    
    /**
     * Registry variables that can only be assigned once
     * @var array 
     */
    protected $finals;

    /**
     * @var string The application mode
     */
    protected $mode;

    /**
     * @var array Event hooks
     */
    protected $hooks = array(
        'ri.before' => array(array()),
        'ri.before.router' => array(array()),
        'ri.before.dispatch' => array(array()),
        'ri.after.dispatch' => array(array()),
        'ri.after.router' => array(array()),
        'ri.after' => array(array())
    );

    /**
     * @var array Output filters
     */
    protected $filters = array();
    
    public function __construct($config=array(), $request=NULL) {
        ;
    }
}