<?php

class Ri_Storage_Session {
    protected $fingerprint;
    protected $sessidExpires=180;

    public function __construct($sessidExpires=180) {
        $this->sessidExpires = $sessidExpires;
        
        if ($this->started()) {
            session_start();
        }
        $this->fingerprint = sha1(Ri_Http_Request::getInstance()->clientIp);
        $this->validate();
    }

    /**
     * With this validation the session is validated aganist the IP from which it was started,
     * and the sessionid stored in the cookie is regenerated each 3 minutes.
     * This way sessions are harder to hijack or stole
     * @return boolean 
     */
    protected function validate() {
        if (isset($_SESSION['PHPSESSID_FINGERPRINT']) && ($_SESSION['PHPSESSID_FINGERPRINT'] != $this->fingerprint)) {
            $this->clear();
            return false;
        } else {
            $_SESSION['PHPSESSID_FINGERPRINT'] = $this->fingerprint;
        }

        //Regenerates the session ID if the current one is expired.
        if (isset($_SESSION['PHPSESSID_EXPIRES']) && (time() >= $_SESSION['PHPSESSID_EXPIRES'])) {
            // Create new session without destroying the old one
            session_regenerate_id(false);
            $_SESSION['PHPSESSID_EXPIRES'] = time() + $this->sessidExpires;
        } elseif (!isset($_SESSION['PHPSESSID_EXPIRES'])) {
            $_SESSION['PHPSESSID_EXPIRES'] = time() + $this->sessidExpires;
        }
        return true;
    }

    public function set($name, $value) {
        $_SESSION[$name]=$value;
    }

    public function fetch($name) {
        if ($this->exists()) {
            return $_SESSION[$name];
        }
        return NULL;
    }

    public function delete($name) {
        if ($this->exists()) {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    public function started() {
        return ri_is_empty(session_id()) != false;
    }

    public function exists($name) {
        return isset($_SESSION[$name]);
    }

    public function clear() {
        if ($this->started()) {
            $last_id = session_id();
            session_unset();
            session_destroy();
            session_start();
            if (session_id() == $last_id) {
                session_regenerate_id(true);
            }
        } else {
            session_start();
        }
    }

}