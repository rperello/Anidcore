<?php

class App extends Base_App {

    /**
     *
     * @return Module_Admin 
     */
    public static function admin() {
        return self::module("admin");
    }

}