<?php

class Ac_JSON_RPC_Response extends Ac_Http_Response {

    public function body($body = null) {
        if (is_array($body))
            $body = json_encode($body);
        return parent::body($body);
    }

    public function sendJSONP($callback, $sendHeaders = true, $cleanOb = true) {
        $_body = $this->body;
        $this->body = $callback . '(' . $this->body . ')';
        $result = $this->send($sendHeaders, $cleanOb);
        $this->body = $_body;
        return $result;
    }

}