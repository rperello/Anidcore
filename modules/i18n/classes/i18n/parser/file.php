<?php

class I18n_Parser_File extends I18n_Parser {

    /**
     *
     * @var string 
     */
    protected $filename = null;

    public function __construct($filename) {
        $this->filename = $filename;
        parent::__construct(unserialize(file_get_contents($filename)));
    }

    /**
     *
     * @return boolean 
     */
    public function save() {
        return file_put_contents($this->filename, serialize($this->texts));
    }

}