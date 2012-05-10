<?php

abstract class Ac_Log {

    abstract public function log($data, $label = null, $options = array());

    abstract public function warn($data, $label = null, $options = array());

    abstract public function info($data, $label = null, $options = array());

    abstract public function error($data, $label = null, $options = array(), $file = null, $line = null);

    abstract public function fatal($data, $label = null, $options = array(), $file = null, $line = null);

    abstract public function write($data, $label = null, $options = array());
}