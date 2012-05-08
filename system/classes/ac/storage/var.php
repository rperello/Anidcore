<?php

class Ac_Storage_Var {

    protected $cache = array();

    public function has($id) {
        return isset($this->cache[$id]);
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function get($id) {
        if (!isset($this->cache[$id]))
            return false;
        else {
            return $this->cache[$id];
        }
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @return boolean
     */
    public function set($id, $value) {
        $this->cache[$id] = $value;
        return true;
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

    public function clear() {
        unset($this->cache);
        $this->cache = array();
    }

}