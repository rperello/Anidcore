<?php

class Ac_Model_Globals_Session extends Ac_Model_Globals {

    protected $sessname;
    protected $sessid_fingerprint;
    protected $sessid_lifetime = 180;

    public function __construct($sessname, $sessid_lifetime = 180, $sessid_fingerprint_data = null) {
        $this->sessname = $sessname;
        $this->sessid_lifetime = $sessid_lifetime;

        if (!empty($sessid_fingerprint_data)) {
            $this->sessid_fingerprint = sha1($sessid_fingerprint_data);
        }
        parent::__construct("_SESSION");
    }

    public function start() {
        if (!$this->isStarted()) {
            session_name($this->sessname);
            session_start();
            $this->validate();
            return true;
        }
        return false;
    }

    public function isStarted() {
        return (session_id() != "");
    }

    /**
     * With this validation the session is validated against a fingerprint, an arbitrary string that could be the IP from which it was started,
     * and the sessionid stored in the cookie is regenerated each sessid_lifetime time.
     * This way sessions are harder to hijack or stole
     * @return boolean 
     */
    protected function validate() {
        if (isset($this->sessid_fingerprint) && (!empty($this->sessid_fingerprint))) {
            if (isset($_SESSION['PHPSESSID_FINGERPRINT']) && ($_SESSION['PHPSESSID_FINGERPRINT'] != $this->sessid_fingerprint)) {
                $this->clear();
                return false;
            } else {
                $_SESSION['PHPSESSID_FINGERPRINT'] = $this->sessid_fingerprint;
            }
        }

        //Regenerates the session ID if the current one is expired.
        if (isset($this->sessid_lifetime) && ($this->sessid_lifetime > 0)) {
            if (isset($_SESSION['PHPSESSID_LIFETIME'])) {
                if (time() >= $_SESSION['PHPSESSID_LIFETIME']) {
                    // Create new session without destroying the old one
                    session_regenerate_id(false);
                    $_SESSION['PHPSESSID_LIFETIME'] = time() + $this->sessid_lifetime;
                }
            } else {
                $_SESSION['PHPSESSID_LIFETIME'] = time() + $this->sessid_lifetime;
            }
        }
        return true;
    }

    public function destroy() {
        if ($this->isStarted()) {
            session_unset();
            session_destroy();
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

}