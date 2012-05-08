<?php

class Ac_Global_Server extends Ac_Global{
    public function __construct() {
        parent::__construct("_SERVER");
    }
}