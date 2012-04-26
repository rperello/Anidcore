<?php

class Ri_Storage{

    /**
     * @var Ri_Storage_Var
     */
    public $var;
    
    /**
     * @var Ri_Storage_Session
     */
    public $session;

    /**
     * @var Ri_Storage_Cookie
     */
    public $cookie;

    /**
     * @var Ri_Storage_Memcache
     */
    public $memcache;

    /**
     * @var Ri_Storage_Db
     */
    public $db;

    /**
     * @var Ri_Storage_File
     */
    public $file;
}