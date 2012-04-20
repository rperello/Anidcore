<?php

interface Ri_Logger {

    public function log($data, $label);

    public function warn($data, $label);

    public function info($data, $label);

    public function error($data, $label);

    public function fatal($data, $label);
}