<?php

abstract class Ac_Log {

    abstract public function log($data, $label = null, $options = array());

    abstract public function warn($data, $label = null, $options = array());

    abstract public function info($data, $label = null, $options = array());

    abstract public function error($data, $label = null, $file = null, $line = null, $options = array());

    abstract public function fatal($data, $label = null, $file = null, $line = null, $options = array());

    abstract public function write($data, $label = null, $options = array());
}