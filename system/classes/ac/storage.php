<?php

class Ac_Storage {

    /**
     * @var Ac_Storage_Var
     */
    public $var;

    /**
     * @var Ac_Storage_Session
     */
    public $session;

    /**
     * @var Ac_Storage_Cookie
     */
    public $cookie;

    /**
     * @var Ac_Storage_Memcache
     */
    public $memcache;

    /**
     * @var Ac_Storage_Db
     */
    public $db;

    /**
     * @var Ac_Storage_File
     */
    public $file;

}