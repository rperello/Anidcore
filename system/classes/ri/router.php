<?php

class Ri_Router extends Ri_Environment {
    public function resolve(){
        $this->context()->hookApply("ri.before.router_resolve", $this);
        //resolve
        $this->context()->hookApply("ri.on.router_resolve", $this);
    }
}