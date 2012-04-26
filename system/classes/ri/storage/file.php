<?php

class Ri_Storage_File {

    /**
     * Cache path
     * @var string
     */
    protected $path;

    /**
     *
     * @var Ri_Storage_Var
     */
    protected $varcache = NULL;

    /**
     *
     * @var Ri_Storage_File 
     */
    protected static $instance = NULL;
    protected $cache_buffer = array();
    protected $cache_buffer_end_queue = array();

    /**
     * Create a new file cache
     * @param string $cachepath
     * @param bool $create
     * @throws Exception 
     */
    public function __construct($cachepath = NULL, $create = false) {
        if (empty($cachepath)) {
            $cachepath = RI_PATH_DATA . "cache" . _DS;
        }

        if ($create) {
            if (!is_dir($cachepath)) {
                if (!mkdir($cachepath, 0755, true)) {
                    throw new Exception('Directory "' . $cachepath . '" does ot exist and could not be created.');
                }
            }
        }

        $this->path = $cachepath;
        $this->varcache = new Ri_Storage_Var();
    }

    /**
     *
     * @return Ri_Storage_File 
     */
    public static function getInstance(array $options = null) {
        if (self::$instance == NULL)
            self::$instance = new self($options);
        return self::$instance;
    }

    /**
     * Add value. Same as store, only will not overwrite existing value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function add($id, $value, $ttl = 0) {
        if ($this->isValid($id) === false) {
            return $this->store($id, $value, $ttl);
        }
        return false;
    }

    /**
     * Removes expired cache entries
     *
     * @return integer the number of entries removed
     */
    public function clear() {
        $erased = 0;
        $files = glob($this->path . '*.cache');
        foreach ($files as $file) {
            if (($handle = $this->fileHandle($file)) !== false) {
                $expires = (int) fgets($handle);
                if ($expires < time()) {
                    fclose($handle);
                    unlink($file);
                    $erased++;
                }
            }
        }
        $this->varcache->clear();
        return $erased;
    }

    /**
     * Delete value from cache
     *
     * @param string $id Value identifier
     * @return boolean
     */
    public function delete($id) {
        $file = $this->fileName($id);
        if (is_file($file)) {
            $this->varcache->delete($id);
            return unlink($file);
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
        if ($this->varcache->isValid($id))
            return $this->varcache->fetch($id);
        $fileName = $this->fileName($id);

        if (!is_readable($fileName)) {
            return false;
        }

        $file = fopen($fileName, 'r');

        $expires = (int) fgets($file);
        if ($expires > time() or $expires === 0) {
            $data = '';
            while (($line = fgets($file)) !== false) {
                $data .= $line;
            }
            fclose($file);
            $value = $data;
            $this->varcache->store($id, $value);
            return $value;
        }
        fclose($file);
        unlink($fileName);
        return false;
    }

    public function isValid($id) {
        return $this->fetch($id) !== false;
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
        $file = $this->fileName($id);
        if ($ttl == 0) {
            $expires = 0;
        } else {
            $expires = time() + (int) $ttl;
        }
        if (file_put_contents($file, $expires
                        . "\n" . $value)) {
            $this->varcache->store($id, $value);
            return true;
        }
    }

    public function fileName($id) {
        $subdir = explode("/", trim($id, "/ "));
        $filename = array_pop($subdir);
        $subdir = implode("/", $subdir);
        if (!empty($subdir))
            $subdir = $subdir . "/";
        $path = $this->path . $subdir;
        if (!is_dir($path))
            mkdir($path, 0755, true);
        return $path . sha1($filename) . '.cache';
    }

    public function start($id = NULL, $lifetime = NULL) {
        $hash = sha1($id . $lifetime);

        if (!isset($this->cache_buffer[$hash])) {
            $this->cache_buffer[$hash] = $id;
        }
        if ($this->isValid($id)) {
            echo $this->fetch($id);
            return false;
        } else {
            ob_start();
            $this->cache_buffer_end_queue[] = array("id" => $id, "lifetime" => $lifetime);
            return true;
        }
    }

    public function end() {
        if (count($this->cache_buffer_end_queue) > 0) {
            $data = array_pop($this->cache_buffer_end_queue);
            $hash = sha1($data["id"] . $data["lifetime"]);
            if (isset($this->cache_buffer[$hash])) {
                if (!$this->isValid($data["id"])) {
                    $content = ob_get_contents();
                    ob_end_clean();
                    $this->store($data["id"], $content, $data["lifetime"]);
                    echo $content;
                }
                unset($this->cache_buffer[$hash]);
            }
        }
    }

    public function getPath() {
        return $this->path;
    }

}