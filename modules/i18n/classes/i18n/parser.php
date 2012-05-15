<?php

abstract class I18n_Parser {

    protected $texts = array();

    public function get($text, $lang = "en", $realm = "__") {
        if (empty($realm))
            $realm = "__";
        else
            $realm = strtolower($realm);
        $lang = strtolower($lang);

        if (!isset($this->texts[$realm])) {
            $this->texts[$realm] = array();
        }

        if (!isset($this->texts[$realm][$lang])) {
            $this->texts[$realm][$lang] = array();
        }
        $hash = md5($text);

        if (isset($this->texts[$realm][$lang][$hash])) {
            return $this->texts[$realm][$lang][$hash];
        }
        return $text;
    }

    public function set($text, $value, $lang = "en", $realm = "__") {
        if (empty($realm))
            $realm = "__";
        else
            $realm = strtolower($realm);
        $lang = strtolower($lang);
    }

    public function remove($text, $lang = "en", $realm = "__") {
        $realm = strtolower($realm);
        $lang = strtolower($lang);

        //delete realm
        if (empty($text) && empty($lang) && (!empty($realm))) {
            if (isset($this->texts[$realm])) {
                unset($this->texts[$realm]);
                return true;
            }
            return false;
        }

        //delete lang from realm
        if (empty($text) && (!empty($lang)) && (!empty($realm))) {
            if (isset($this->texts[$realm]) && isset($this->texts[$realm][$lang])) {
                unset($this->texts[$realm][$lang]);
                return true;
            }
            return false;
        }

        //delete lang from all realms
        if (empty($text) && (!empty($lang)) && empty($realm)) {
            $lang_deleted = false;
            foreach ($this->texts as $realm => $lngs) {
                if (isset($lngs[$lang])) {
                    unset($this->texts[$realm][$lang]);
                    $lang_deleted = true;
                }
            }
            return $lang_deleted;
        }

        //delete text
        if (!empty($text)) {
            $hash = md5($text);
            if (isset($this->texts[$realm]) && isset($this->texts[$realm][$lang]) && isset($this->texts[$realm][$lang][$hash])) {
                unset($this->texts[$realm][$lang][$hash]);
                return true;
            }
        }

        return false;
    }

    abstract public function save();
}

abstract class I18n_Parser2 {

    protected $texts = array();

    public function key($text, $lang = "en", $realm = "_") {
        if (empty($realm))
            $realm = "__";
        else
            $realm = strtolower($realm);
        $lang = strtolower($lang);
        return implode(".", array($realm, $lang, md5($text)));
    }

    public function text($text, $lang = "en", $realm = "_") {
        $k = $this->key($text, $lang, $realm);
        if (isset($this->texts[$k])) {
            return $this->texts[$k];
        }
        return $text;
    }

    public function delete($text, $lang = "en", $realm = "_") {
        $k = $this->key($text, $lang, $realm);
        if (isset($this->texts[$k])) {
            return $this->texts[$k];
        }
        return $text;
    }

    public function modify($text, $value, $lang = null, $realm = null) {
        
    }

    abstract public function save();
}