<?php

class Ac_Cache_File extends Ac_Cache {

    /**
     * Cache path
     * @var string
     */
    public $path;

    /**
     * Variable cache
     * @var array
     */
    protected $vcache = null;
    protected $buffer = array();
    protected $buffer_queue = array();

    /**
     * Create a new file cache
     * @param string $cache_path
     * @throws Exception 
     */
    public function __construct($cache_path) {
        if (!is_dir($cache_path)) {
            if (!mkdir($cache_path, 0755, true)) {
                throw new Exception('Directory "' . $cache_path . '" does ot exist and could not be created.');
            }
        }

        $this->path = $cache_path;
        $this->vcache = array();
    }

    public function has($id) {
        return $this->get($id) !== false;
    }

    /**
     * Fetch value
     *
     * @param string $id Value identifier
     * @return mixed Returns value or false
     */
    public function get($id) {
        if (isset($this->vcache[$id]))
            return $this->vcache[$id];
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
            $this->vcache[$id] = $value;
            return $value;
        }
        fclose($file);
        unlink($fileName);
        return false;
    }

    /**
     * Store value
     *
     * @param string $id Value identifier
     * @param mixed $value Value to be stored
     * @param integer $ttl Cache time to live
     * @return boolean
     */
    public function set($id, $value, $ttl = 0) {
        $file = $this->fileName($id);
        if ($ttl == 0) {
            $expires = 0;
        } else {
            $expires = time() + (int) $ttl;
        }
        if (file_put_contents($file, $expires . "\n" . $value)) {
            $this->vcache[$id] = $value;
            return true;
        }
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
            unset($this->vcache[$id]);
            return unlink($file);
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
        $this->vcache = array();
        return $erased;
    }

    /**
     *
     * @return boolean 
     */
    public function clearVarcache() {
        $this->vcache = array();
        return true;
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

    public function bufferStart($id = null, $lifetime = null) {
        $hash = sha1($id . $lifetime);

        if (!isset($this->buffer[$hash])) {
            $this->buffer[$hash] = $id;
        }
        if ($this->has($id)) {
            echo $this->get($id);
            return false;
        } else {
            ob_start();
            $this->buffer_queue[] = array("id" => $id, "lifetime" => $lifetime);
            return true;
        }
    }

    public function bufferEnd() {
        if (count($this->buffer_queue) > 0) {
            $data = array_pop($this->buffer_queue);
            $hash = sha1($data["id"] . $data["lifetime"]);
            if (isset($this->buffer[$hash])) {
                if (!$this->has($data["id"])) {
                    $content = ob_get_contents();
                    ob_end_clean();
                    $this->set($data["id"], $content, $data["lifetime"]);
                    echo $content;
                }
                unset($this->buffer[$hash]);
            }
        }
    }

}