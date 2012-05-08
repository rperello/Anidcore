<?php

class Ac_Cache_Memcache extends Ac_Cache {

    /**
     *
     * @var MemCache 
     */
    protected $memcache = null;

    /**
     * Variable cache
     * @var array
     */
    protected $vcache = array();
    protected $config = array(
        "enabled" => true,
        "host" => "127.0.0.1",
        "port" => "11211",
        "default_ttl" => 86400, // 1 day
        "autoconnect" => false
    );
    protected $connected = false;

    public function __construct($config) {
        $this->config = array_merge($this->config, $config);
        if ($this->isEnabled()) {
            $this->memcache = new Memcache;
            $this->vcache = array();

            if ($config["autoconnect"] == true) {
                $this->connect();
            }
        }
    }

    public function has($id) {
        if (!$this->isEnabled())
            return false;

        return $this->get($id) !== false;
    }

    public function get($id) {
        if (!$this->isEnabled())
            return false;
        if (!isset($this->vcache[$id])) {
            $this->vcache[$id] = $this->memcache->get($id);
        }

        return $this->vcache[$id];
    }

    public function set($id, $value, $ttl = 0) {
        if (!$this->isEnabled())
            return false;
        $this->vcache[$id] = $value;

        if ($ttl === null)
            $ttl = $this->config["default_ttl"];

        $stored = $this->memcache->replace($id, $value, $flag = 0, $ttl);
        if ($stored == false) {
            $stored = $this->memcache->set($id, $value, $flag = 0, $ttl);
        }
        return $stored;
    }

    public function delete($id) {
        if (!$this->isEnabled())
            return false;

        if (isset($this->vcache[$id])) {
            unset($this->vcache[$id]);
        }
        if ($this->memcache->get($id)) {
            $this->memcache->delete($id);
        }
    }

    public function clear() {
        if (!$this->isEnabled())
            return false;
        return $this->memcache->flush() && $this->clearVarcache();
    }

    /**
     *
     * @return boolean 
     */
    public function clearVarcache() {
        if (!$this->isEnabled())
            return false;
        $this->vcache = array();
        return true;
    }

    /**
     *
     * @return boolean 
     */
    public function connect() {
        if (!$this->isEnabled())
            return false;

        $this->connected = $this->memcache->connect($this->config["host"], $this->config["port"]);

        return $this->connected;
    }

    /**
     *
     * @return boolean 
     */
    public function isConnected() {
        if (!$this->isEnabled())
            return false;

        return $this->connected;
    }

    /**
     *
     * @return boolean 
     */
    public function disconnect() {
        if (!$this->isEnabled())
            return false;

        return $this->memcache->close();
    }

    public function enable() {
        $this->config["enabled"] = true;
    }

    public function disable() {
        $this->config["enabled"] = false;
    }

    /**
     *
     * @return boolean 
     */
    public function isEnabled() {
        return ($this->config["enabled"] === true);
    }

}