<?php

class Ri_Storage_Var extends Ri_Cache {

    protected $cache = array();

    /**
     *
     * @var Ri_Storage_Var 
     */
    protected static $instance = NULL;

    /**
     *
     * @return Ri_Storage_Var 
     */
    public static function getInstance() {
        if (self::$instance == NULL)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function store($id, $value, $ttl = 0) {
        $expires = ($ttl == 0) ? 0 : (time() + $ttl);
        $this->cache[$id] = array($value, $expires);
        return true;
    }

    /**
     * Add value. Same as store, but will not overwrite an existing value.
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0) {
        if (!$this->isValid($id)) {
            return $this->set($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function fetch($id) {
        if (!$this->isValid($id))
            return false;
        else
            return $this->cache[$id][0];
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id) {
        if (isset($this->cache[$id])) {
            unset($this->cache[$id]);
            return true;
        }
        return false;
    }

    public function isValid($id) {
        if (!isset($this->cache[$id])) {
            return false;
        }
        if (($this->cache[$id][1] > 0) && ($this->cache[$id][1] >= time())) {
            unset($this->cache[$id]);
            return false;
        }
        return true;
    }

    public function clear() {
        unset($this->cache);
        $this->cache = array();
    }

}