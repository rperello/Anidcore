<?php

class Ri_Log {

    public function log($data, $label) {
        $this->file("Rino LOG: " . $label . " : " . print_r($data, true), "log.log");
    }

    public function warn($data, $label) {
        $this->file("Rino WARN: " . $label . " : " . print_r($data, true), "warn.log");
    }

    public function info($data, $label) {
        $this->file("Rino INFO: " . $label . " : " . print_r($data, true), "info.log");
    }

    public function error($data, $label, $file, $line) {
        $this->file("Rino ERROR (File $file; Line $line): " . $label . " : " . print_r($data, true), "error.log");
    }

    public function fatal($data, $label, $file, $line) {
        $this->file("Rino FATAL ERROR (File $file; Line $line): " . $label . " : " . print_r($data, true), "fatal.log");
        throw new RuntimeException("Rino FATAL ERROR (File $file; Line $line): " . $label . " : " . print_r($data, true));
    }

    public function file($message, $filename = "console.log") {
        $message = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
        $fname = RI_PATH_LOGS . $filename;
        if (!file_exists($fname)) {
            return file_put_contents($fname, $message, 0);
        } else {
            return file_put_contents($fname, $message, FILE_APPEND);
        }
    }

}