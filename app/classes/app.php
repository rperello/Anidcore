<?php

class App{
    public static function beforeRouterResolve(){
        if(Ac::request()->resource=='test2'){
            Ac::request()->requestMethod="PUT";
        }
    }
}