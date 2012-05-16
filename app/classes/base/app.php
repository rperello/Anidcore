<?php

class Base_App extends Ac{
    protected $current_language = null;
    protected $current_document = null;

    /**
     *
     * @return Module_App
     */
    public static function main() {
        return self::module("app");
    }
    
    public static function language($name = null){
        
    }
    
    public static function document($id = null){
        
    }
}