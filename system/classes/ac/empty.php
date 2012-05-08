<?php

/**
 * Empty object that always returns null 
 */
final class Ac_Empty {

    public function __call($name, $arguments) {
        return null;
    }

    public function __invoke() {
        return null;
    }

    public function __set($name, $value) {
        return null;
    }

    public function __get($name) {
        return null;
    }

    public function __isset($name) {
        return null;
    }

    public function __unset($name) {
        return null;
    }

    public function __toString() {
        return null;
    }

}