<?php

/**
 * Empty object that always returns null 
 */
final class Ri_Empty {

    public function __call($name, $arguments) {
        return NULL;
    }

    public function __invoke() {
        return NULL;
    }

    public function __set($name, $value) {
        return NULL;
    }

    public function __get($name) {
        return NULL;
    }

    public function __isset($name) {
        return NULL;
    }

    public function __unset($name) {
        return NULL;
    }

    public function __toString() {
        return NULL;
    }

}