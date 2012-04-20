<?php

####################
## ri_crypto
####################

/**
 * Encrypts an string using MCRYPT_RIJNDAEL_256 and MCRYPT_MODE_ECB
 * 
 * @param string $text The RAW string
 * @param string $key The key with which the data will be encrypted.
 * @return string|false The encrypted and base64-safe-encoded string (safe for urls)
*/
function ri_crypto_encrypt($text, $key=NULL) {
    if (empty($text)) {
        return false;
    }
    if(empty($key)) $key=ac_config("salt");
    
    return ri_base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))), true);

}

/**
 * Decrypts an string previously encrypted using ri_crypto_encrypt
 * 
 * @param string $encrypted The RAW encrypted string
 * @param string $salt The key with which the data was encrypted.
 * @return string|false The decrypted string 
*/
function ri_crypto_decrypt($encrypted, $key=NULL) {
    if (empty($encrypted)) {
        return false;
    }
    if(empty($key)) $key=ac_config("salt");
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), ri_base64_decode($encrypted, true), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
}

####################
## ri_base64
####################

/**
* Returns an encoded string, safe for URLs
* @param string $string
* @param bool $urlSafe
* @return string 
*/
function ri_base64_encode($string, $urlSafe=false) {
    $data = base64_encode($string);
    if($urlSafe){
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    }
    return $data;
}

/**
*
* @param string $string String encoded using base64_safe_encode()
* @param bool $urlSafe
* @return string 
*/
function ri_base64_decode($string, $urlSafe=false) {
    if($urlSafe){
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
    }else{
        $data = $string;
    }
    return base64_decode($data);
}


####################
## ri_jsonrpc
####################

function ri_jsonrpc_request($id, $method, $params = NULL, $version = "2.0") {
    $message = array(
        "jsonrpc" => $version,
        "method" => $method,
        "params" => $params,
        "id" => (!(empty($id)) ? $id : str_random(16))
    );
    return json_encode($message);
}

function ri_jsonrpc_notification($method = "", $params = NULL, $version = "2.0") {
    $message = array(
        "jsonrpc" => $version,
        "method" => $method,
        "params" => $params
    );
    return json_encode($message);
}

function ri_jsonrpc_result($id, $result = NULL, $version = "2.0") {
    $message = array(
        "jsonrpc" => $version,
        "result" => $result,
        "id" => $id
    );
    return json_encode($message);
}

function ri_jsonrpc_error($id = NULL, $code = 1, $message = "", $data = NULL, $version = "2.0") {
    $error = array(
        "code" => $code,
        "message" => $message
    );
    if ($data !== NULL)
        $error["data"] = $data;
    $message = array(
        "jsonrpc" => $version,
        "error" => $error,
        "id" => $id
    );
    return json_encode($message);
}

####################
## ri_str
####################

/**
 * Generates a random string
 * @param int $length
 * @param string $chars
 * @return string
 */
function ri_str_random($length = 32, $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789") {
    $plength = strlen($chars);
    mt_srand((double) microtime() * 10000000);
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= $chars[mt_rand(0, $plength - 1)];
    }
    return $str;
}

/**
 * Cleans a string with the specified rules
 * @param string $str
 * @param bool $remove_quotes
 * @param bool $remove_html
 * @param bool $remove_backslashes
 * @param bool $remove_whitespace
 * @return string 
 */
function ri_str_clean($str, $remove_quotes = true, $remove_html = true, $remove_backslashes = true, $remove_whitespace = true) {
    if ($remove_html) {
        $str = strip_tags($str);
    }
    if ($remove_quotes) {
        $str = str_replace(array("'", '"', '´', '`'), "", $str);
    }
    if ($remove_backslashes) {
        $str = str_replace(array("\\"), "", $str);
    }
    if ($remove_whitespace) {
        $str = trim($str, "\n ");
    }
    return $str;
}


####################
## ri_arr
####################

/**
 * Checks if a variable exists inside an array and matches the given php filter or regular expression
 * @param array $arr Associated array of values
 * @param string $key Array key name
 * @param mixed $default Default value if the variable is not set or regexp is false
 * @param mixed $filter FILTER_* constant value or regular expression
 * @return mixed
 */
function ri_arr_value($arr, $key, $default = false, $filter = NULL) {
    if (!is_array($arr))
        return $default;
    if (isset($arr[$key])) {
        if ($filter != NULL) {
            if (is_string($filter) && ($filter{0} == "/")) {
                //regexp
                return (preg_match($filter, $arr[$key]) > 0) ? $arr[$key] : $default;
            } elseif (is_int($filter)) {
                return filter_var($arr[$key], $filter);
            }
        }else
            return $arr[$key];
    }else
        return $default;
}

function ri_arr_first($arr) {
    return array_shift($arr);
}

function ri_arr_last($arr) {
    return array_pop($arr);
}

function ri_arr_some($arr, $count, $offset = 0) {
    if (!is_array($arr))
        return $arr;
    $items = array();
    $i = 0;
    foreach ($arr as $key => $item) {
        if (($i >= $offset) && (count($items) <= $count - 1)) {
            $items[$key] = $item;
        }
        $i++;
    }
    return $items;
}

function ri_arr_sort_by($field, &$arr, $sorting = SORT_ASC, $case_insensitive = true) {
    if (is_array($arr) && (count($arr) > 0)) {
        if ($case_insensitive == true)
            $strcmp_fn = "strnatcasecmp";
        else
            $strcmp_fn = "strnatcmp";

        if ($sorting == SORT_ASC) {
            $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b) && isset($a->' . $field . ') && isset($b->' . $field . ')){
                    return ' . $strcmp_fn . '($a->' . $field . ', $b->' . $field . ');
                }else if(is_array($a) && is_array($b) && isset($a["' . $field . '"]) && isset($b["' . $field . '"])){
                    return ' . $strcmp_fn . '($a["' . $field . '"], $b["' . $field . '"]);
                }else return 0;
            ');
        } else {
            $fn = create_function('$a,$b', '
                if(is_object($a) && is_object($b) && isset($a->' . $field . ') && isset($b->' . $field . ')){
                    return ' . $strcmp_fn . '($b->' . $field . ', $a->' . $field . ');
                }else if(is_array($a) && is_array($b) && isset($a["' . $field . '"]) && isset($b["' . $field . '"])){
                    return ' . $strcmp_fn . '($b["' . $field . '"], $a["' . $field . '"]);
                }else return 0;
            ');
        }
        usort($arr, $fn);
        return true;
    } else {
        return false;
    }
}

/**
 * Returns an array containing the values of the specified field
 * @param array $arr array of objects or/and associated arrays
 * @param string $field
 * @return array
 */
function ri_arr_field_values($arr, $fieldName) {
    if(empty($arr) || (!is_array($arr))) return array();
    
    $values = array();
    foreach ($arr as $item) {
        if(is_array($item) && isset($item[$fieldName])){
            $values[] = $item[$fieldName];
        }elseif(is_object() && isset($item->$fieldName)){
            $values[] = $item->$fieldName;
        }
    }
    return $values;
}

####################
## ri_dir
####################



####################
## ri_file
####################



####################
## ri_reflection
####################

/**
 * Retrieves a class constant using reflection
 * @param string $class_name
 * @param string $constant_name
 * @return mixed
 */
function ri_reflection_class_constant($class_name, $constant_name) {
    $reflect = new ReflectionClass($class_name);
    $constants = $reflect->getConstants();

    return $constants[$constant_name];
}

/**
 * Retrieves class constants using reflection
 * @param string $class_name
 * @return mixed
 */
function ri_reflection_class_constants($class_name) {
    $reflect = new ReflectionClass($class_name);
    $constants = $reflect->getConstants();

    return $constants;
}

/**
 * Retrieves a class property or constant using eval functions
 * @param string $property
 * @param string $class_name
 * @return mixed
 */
function ri_reflection_static_property($property, $class_name) {
    if (defined($class_name . '::' . $property)) {
        return eval("return {$class_name}::{$property};");
    } elseif (property_exists($class_name, $property)) {
        return eval("return {$class_name}::\${$property};");
    } else {
        return NULL;
    }
}