<?php

class Ri_Storage_Memcache extends Ri_Cache {

    /**
     *
     * @var MemCache 
     */
    protected $memcache = null;

    /**
     *
     * @var Ri_Storage_Var 
     */
    protected $vcache = null;
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
            $this->vcache = new Ri_Storage_Var();

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
        if (!$this->vcache->has($id)) {
            $this->vcache->set($id, $this->memcache->get($id));
        }

        return $this->vcache->get($id);
    }

    public function set($id, $value, $ttl = 0) {
        if (!$this->isEnabled())
            return false;
        $this->vcache->set($id, $value);

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

        if ($this->vcache->has($id)) {
            $this->vcache->delete($id);
        }
        if ($this->memcache->has($id)) {
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
        return $this->vcache->clear();
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