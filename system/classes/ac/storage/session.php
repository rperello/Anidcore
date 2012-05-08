<?php

class Ac_Storage_Session {

    protected $name;
    protected $fingerprint;
    protected $sessid_lifetime = 180;

    public function __construct($name, $sessid_lifetime = 180, $fingerprint_data = null) {
        $this->name = $name;
        $this->sessid_lifetime = $sessid_lifetime;

        if (!empty($fingerprint_data)){
            $this->fingerprint = sha1($fingerprint_data);
        }
    }

    public function has($name) {
        if (!$this->isStarted())
            return false;
        else
            return isset($_SESSION[$name]);
    }

    public function get($name) {
        if ($this->has($name)) {
            return $_SESSION[$name];
        }
        return null;
    }

    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public function delete($name) {
        if ($this->has($name)) {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    public function clear() {
        if ($this->isStarted()) {
            $last_id = session_id();
            $this->destroy();
            $this->start();
            //we ensure that it's a whole new session
            if (session_id() == $last_id) {
                session_regenerate_id(true);
            }
            return true;
        }
        return false;
    }

    public function start() {
        if (!$this->isStarted()) {
            session_name($this->name);
            session_start();
            $this->validate();
            return true;
        }
        return false;
    }

    public function isStarted() {
        return (session_id() != "");
    }

    public function destroy() {
        if ($this->isStarted()) {
            session_unset();
            session_destroy();
        }
        return false;
    }

    /**
     * With this validation the session is validated against a fingerprint, an arbitrary string that could be the IP from which it was started,
     * and the sessionid stored in the cookie is regenerated each sessid_lifetime time.
     * This way sessions are harder to hijack or stole
     * @return boolean 
     */
    protected function validate() {
        if (isset($this->fingerprint) && (!empty($this->fingerprint))) {
            if (isset($_SESSION['PHPSESSID_FINGERPRINT']) && ($_SESSION['PHPSESSID_FINGERPRINT'] != $this->fingerprint)) {
                $this->clear();
                return false;
            } else {
                $_SESSION['PHPSESSID_FINGERPRINT'] = $this->fingerprint;
            }
        }

        //Regenerates the session ID if the current one is expired.
        if (isset($this->sessid_lifetime) && ($this->sessid_lifetime > 0)) {
            if (isset($_SESSION['PHPsessid_lifetime'])) {
                if (time() >= $_SESSION['PHPsessid_lifetime']) {
                    // Create new session without destroying the old one
                    session_regenerate_id(false);
                    $_SESSION['PHPsessid_lifetime'] = time() + $this->sessid_lifetime;
                }
            } else {
                $_SESSION['PHPsessid_lifetime'] = time() + $this->sessid_lifetime;
            }
        }
        return true;
    }

}