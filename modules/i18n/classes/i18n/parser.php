<?php

abstract class I18n_Parser {

    protected $texts = array();

    public function __construct($texts = array()) {
        $this->texts = $texts;
    }

    /**
     *
     * @param string $text
     * @param string $realm
     * @return string 
     */
    public function get($text, $realm = "_") {
        if (empty($realm))
            $realm = "_";
        $realm = strtolower($realm);

        if (!isset($this->texts[$realm])) {
            return $text;
        }

        $hash = md5($text);

        if (isset($this->texts[$realm][$hash])) {
            return $this->texts[$realm][$hash];
        }
        return $text;
    }

    /**
     *
     * @param string $text
     * @param string $value
     * @param string $realm
     */
    public function set($text, $value, $realm = "_") {
        if (empty($realm))
            $realm = "_";
        $realm = strtolower($realm);

        if (!isset($this->texts[$realm])) {
            $this->texts[$realm] = array();
        }

        $hash = md5($text);
        $this->texts[$realm][$hash] = $value;
    }

    /**
     *
     * @param string $text
     * @param string $realm
     * @return boolean 
     */
    public function remove($text, $realm = "_") {
        if (empty($realm))
            $realm = "_";
        $realm = strtolower($realm);

        if (!empty($text)) {
            $hash = md5($text);
            if (isset($this->texts[$realm]) && isset($this->texts[$realm][$hash])) {
                unset($this->texts[$realm][$hash]);
                return true;
            }
        }

        return false;
    }

    public function __sleep() {
        return array("texts");
    }

    /**
     *
     * @return boolean 
     */
    abstract public function save();
}