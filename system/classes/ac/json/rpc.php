<?php

class Ac_JSON_RPC {
    const ERROR_INVALID_REQUEST = -32600;
    const ERROR_METHOD_NOT_FOUND = -32601;
    const ERROR_INVALID_PARAMS = -32602;
    const ERROR_EXCEPTION = -32099;

    public static function request($id, $method, $params = null, $version = "2.0") {
        return array(
            "jsonrpc" => $version,
            "method" => $method,
            "params" => $params,
            "id" => $id
        );
    }

    public static function notification($method = "", $params = null, $version = "2.0") {
        return array(
            "jsonrpc" => $version,
            "method" => $method,
            "params" => $params
        );
    }

    public static function result($id, $result = null, $version = "2.0") {
        return array(
            "jsonrpc" => $version,
            "result" => $result,
            "id" => $id
        );
    }

    public static function error($id = null, $code = -1, $message = "", $data = null, $version = "2.0") {
        $error = array(
            "code" => $code,
            "message" => $message
        );
        if ($data !== null)
            $error["data"] = $data;

        return array(
            "jsonrpc" => $version,
            "error" => $error,
            "id" => $id
        );
    }

}