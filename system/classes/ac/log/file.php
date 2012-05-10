<?php

class Ac_Log_File extends Ac_Log {

    public function log($data, $label = null, $options = array()) {
        $this->write($data, "[LOG] " . $label . " : ", array_merge(array(
                    "func" => __FUNCTION__,
                    "filename" => "debug.log",
                    "use_timestamp" => true,
                    "use_monthfolders" => false
                        ), $options));
    }

    public function warn($data, $label = null, $options = array()) {
        $this->write($data, "[WARNING] " . $label . " : ", array_merge(array(
                    "func" => __FUNCTION__,
                    "filename" => "warnings.log",
                    "use_timestamp" => true,
                    "use_monthfolders" => false
                        ), $options));
    }

    public function info($data, $label = null, $options = array()) {
        $this->write($data, "[INFO] " . $label . " : ", array_merge(array(
                    "func" => __FUNCTION__,
                    "filename" => "debug.log",
                    "use_timestamp" => true,
                    "use_monthfolders" => false
                        ), $options));
    }

    public function error($data, $label = null, $file = null, $line = null, $options = array()) {
        $label = "[ERROR] '" . $label . " : ";
        $options = array_merge(array(
            "func" => __FUNCTION__,
            "append" => "' in $file on line $line",
            "filename" => "errors.log",
            "use_timestamp" => true,
            "use_monthfolders" => false,
            "throw_exception" => true,
                ), $options);
        $this->write($data, $label, $options);

        if ($options["throw_exception"])
            throw new Exception("Anidcore " . $label . " : " . print_r($data, true) . "' ");
    }

    public function fatal($data, $label = null, $file = null, $line = null, $options = array()) {
        $label = "[FATAL] '" . $label . " : ";
        $options = array_merge(array(
            "func" => __FUNCTION__,
            "append" => "' in $file on line $line",
            "filename" => "errors.log",
            "use_timestamp" => true,
            "use_monthfolders" => false,
            "throw_exception" => true,
                ), $options);
        $this->write($data, $label, $options);

        if ($options["throw_exception"])
            throw new RuntimeException("Anidcore " . $label . " : " . print_r($data, true) . "' ");
    }

    public function write($data, $label = null, $options = array()) {
        $options = array_merge(array(
            "func" => "log",
            "append" => "",
            "filename" => "debug.log",
            "use_timestamp" => true,
            "use_monthfolders" => true,
                ), $options);

        $message = $label . print_r($data, true) . $options["append"];
        $filename = $options["filename"];

        if ($options["use_timestamp"])
            $message = "[" . date("d-M-Y H:i:s") . "] " . $message;

        if ($options["use_monthfolders"])
            $path = AC_PATH_LOGS . date("Y") . _DS . strtolower(date("M")) . _DS;
        else
            $path = AC_PATH_LOGS;

        if (!is_dir($path))
            mkdir($path, 0770, true);

        $fname = $path . $filename;
        if (!file_exists($fname)) {
            return file_put_contents($fname, $message . "\n", 0);
        } else {
            return file_put_contents($fname, $message . "\n", FILE_APPEND);
        }
    }

}