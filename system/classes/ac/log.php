<?php

abstract class Ac_Log {

    abstract public function log($data, $label = null);

    abstract public function warn($data, $label = null);

    abstract public function info($data, $label = null);

    abstract public function error($data, $label = null, $file = null, $line = null);

    abstract public function fatal($data, $label = null, $file = null, $line = null);
}