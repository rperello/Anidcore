<?php

class Ac_Logger {

    public function log($data, $label) {
        $this->file("LOG: " . $label . " : " . print_r($data, true), "debug.log", true, false);
    }

    public function warn($data, $label) {
        $this->file($label . " : " . print_r($data, true), "warnings.log", true, false);
    }

    public function info($data, $label) {
        $this->file("INFO: " . $label . " : " . print_r($data, true), "debug.log", true, false);
    }

    public function error($data, $label, $file, $line) {
        $this->file("Anidcore ERROR: '" . $label . " : " . print_r($data, true) . "' in $file on line $line", "errors.log", true, false);
    }

    public function fatal($data, $label, $file, $line) {
        $this->file("Anidcore FATAL ERROR: '" . $label . " : " . print_r($data, true) . "' in $file on line $line", "errors.log", true, false);
        throw new RuntimeException($label . " : " . print_r($data, true));
    }

    public function file($message, $filename = "debug.log", $use_timestamp = true, $use_monthly_folders = true) {
        if ($use_timestamp)
            $message = "[" . date("d-M-Y H:i:s") . "] " . $message;

        if ($use_monthly_folders)
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