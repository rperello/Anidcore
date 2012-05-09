<?php

define("AC_CHARS_HEXADECIMAL", "abcdef0123456789");
define("AC_CHARS_ALPHANUMERIC", "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
define("AC_CHARS_SYMBOLS", "{}()[]<>!?|@#%&/=^*;,:.-_+");

####################
## ac_arr
####################

/**
 * Checks if a variable exists inside an array and matches the given php filter or regular expression
 * @param array $arr Associated array of values
 * @param string $key Array key name
 * @param mixed $default Default value if the variable is not set or regexp is false
 * @param mixed $filter FILTER_* constant value or regular expression
 * @return mixed
 */
function ac_arr_value($arr, $key, $default = false, $filter = null) {
    if (!is_array($arr))
        return $default;
    if (isset($arr[$key])) {
        if ($filter != null) {
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

function ac_arr_first($arr) {
    return reset($arr);
}

function ac_arr_last($arr) {
    return end($arr);
}

/**
 * Sorts an array of associative arrays or objects by field
 * @param string $field
 * @param array $arr
 * @param int $sorting
 * @param boolean $case_insensitive
 * @return boolean 
 */
function ac_arr_sort_by($field, &$arr, $sorting = SORT_ASC, $case_insensitive = true) {
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
function ac_arr_field_values($arr, $fieldName) {
    if (empty($arr) || (!is_array($arr)))
        return array();

    $values = array();
    foreach ($arr as $item) {
        if (is_array($item) && isset($item[$fieldName])) {
            $values[] = $item[$fieldName];
        } elseif (is_object() && isset($item->$fieldName)) {
            $values[] = $item->$fieldName;
        }
    }
    return $values;
}

####################
## ac_str
####################

/**
 * Returns a camelized string. Detects some word separators by default.
 * @param string $str
 * @param array $separators
 * @param array $replacements
 * @return string
 */
function ac_str_camelize($str) {
    $str = ac_str_slug($str, ' ');
    $str = trim(implode('', explode(' ', ucwords(strtolower($str)))));
    return lcfirst($str);
}

/**
 * @param string $str Camelized string
 * @param string $delimiter Used for separating words
 * @return string 
 */
function ac_str_uncamelize($str, $delimiter = " ") {
    $str = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $delimiter . '$0', $str));
    return strtolower($str);
}

/**
 * Cuts a string if it exceeds the given $length, and appends the $append param
 * @param string $str Original string
 * @param int $length Max length
 * @param string $append String that will be appended if the original string exceeds $length
 * @return string 
 */
function ac_str_reduce($str, $length, $append = "") {
    if (($length > 0) && (strlen($str) > $length)) {
        return substr($str, 0, $length) . $append;
    }else
        return $str;
}

/**
 * Cuts a string by entire words if it exceeds the given $length, and appends the $append param
 * @param string $str Original string
 * @param int $length Max length
 * @param string $append String that will be appended if the original string exceeds $length
 * @return string 
 */
function ac_str_reduce_words($str, $length, $append = "") {
    $str2 = preg_replace('/\s\s+/', ' ', $str);
    $words = explode(" ", $str2);
    if (($length > 0) && (count($words) > $length)) {
        return implode(" ", array_slice($words, 0, $length)) . $append;
        //return substr($str, 0, $length).$append;
    }else
        return $str;
}

function ac_str_replace_repeated($str, $char, $replacement = null) {
    return preg_replace('/' . $char . $char . '+/', $replacement, $str);
}

/**
 * Converts any string to a friendly-url string
 * @param string $str
 * @param string $delimiter
 * @param array $replace Characters to be replaced with delimiter
 * @return string
 */
function ac_str_slug($str, $delimiter = '-', $replace = array()) {
    if (!empty($replace)) {
        $str = str_replace((array) $replace, ' ', $str);
    }

    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '- '));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
}

/**
 * Converts a friendly url-like formatted string to a human readable string.
 * Detects '-' and '_' word separators by default.
 * @param string $str
 * @param string $delimiter
 * @return string
 */
function ac_str_unslug($str, $delimiter = "-") {
    $str = str_replace($delimiter, " ", $str);
    return ucfirst($str);
}

/**
 * Generates a random string
 * @param int $length
 * @param string $chars
 * @return string
 */
function ac_str_random($length = 32, $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789") {
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
function ac_str_clean($str, $remove_quotes = true, $remove_html = true, $remove_backslashes = true, $remove_whitespace = true) {
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

function ac_str_latin1_to_utf8($str) {
    return utf8_encode(utf8_decode($str));
}

function ac_str_remove_fontstyles($str) {
    $patterns = array();

    //CSS attributes
    $patterns[] = '/style\=\"[^\"]*(\s*(font-size)\s*:\s*[0-9]{1,}\s*(px|em|pt|%|in|cm|mm|ex|pc)\s*;*\s*)/i';
    $patterns[] = '/(\s*(font-family|font)\s*:\s*[\"\',\.\s\-\_0-9a-zA-Z]{1,}\s*;*\s*)/i';
    //$patterns[] = '/(\s*(background|color|background-image|background-color)\s*:\s*[0-9a-zA-Z#\(\),\.%\s\"\'\-\_\/\\\\]{1,}\s*;*\s*)/i';
    //$patterns[]= '/(\s*(padding|padding-top|padding-right|padding-bottom|padding-left)\s*:\s*[0-9]{1,}\s*(px|em|pt|%|in|cm|mm|ex|pc)\s*;*\s*)/i';
    $str = preg_replace($patterns, "", $str);

    //HTML tags
    $str = preg_replace('/\s*<font[^>]*>\s*(.*)\s*<\/font>\s*/Ui', "//1", $str);

    //Style sheet
    $str = preg_replace('/(\s*<style[^>]*>[\s\S]*<\/style>\s*)/Ui', "", $str);

    //Links
    $str = preg_replace('/(\s*<link[^>]*\/?' . '>\s*)/i', "", $str);

    return $str;
}

/**
 * Joins one or more strings if they are not empty
 * @param string $glue
 * @param string $str1, $str2, $str3, ...
 * @return string 
 */
function ac_str_join($glue = ",") {
    $args = func_get_args();
    $glue = $args[0];
    unset($args[0]);
    foreach ($args as $i => $arg) {
        $arg = trim($arg);
        if (($arg == null) || ($arg == "") || ($arg == " ")) {
            unset($args[$i]);
        }
    }
    return implode($glue, $args);
}

####################
# ac_format
####################

/**
 * Converts cents to currency (with two decimals)
 * @param int $price Price in cents (integer)
 * @param string $dec_point
 * @param string $thousands_sep
 * @return string 
 */
function ac_format_currency($price, $dec_point = ",", $thousands_sep = "") {
    return number_format($price / 100, 2, $dec_point, $thousands_sep);
}

/**
 * Converts a base16 string to a binary string
 * @param string $hexdata
 * @return string
 */
function ac_format_hex2bin($hexdata) {
    $bindata = "";

    for ($i = 0; $i < strlen($hexdata); $i+=2) {
        $bindata.=chr(hexdec(substr($hexdata, $i, 2)));
    }

    return $bindata;
}

####################
# ac_date
####################

function ac_date_utc($time = null) {
    if ($time == null)
        $time = time();
    return date('Y-m-d\\TH:i:s\\.000\\Z', $time - date("Z"));
}

function ac_date_from_ts($timestamp, $format = "d.m.Y") {
    return date($format, strtotime($timestamp));
}

function ac_date_daysbetween($from, $to) {
    $start = strtotime($from);
    $end = strtotime($to);
    $num_days = round(($end - $start) / 86400 /* day in seconds */) + 1;
    $days = array();
    for ($d = 0; $d < $num_days; $d++) {
        $days[] = $start + ($d * 86400);
    }
    // Return days
    return $days;
}

function ac_date_in_range($date, $from, $to) {
    $times = ac_date_daysbetween($from, $to);
    return in_array(strtotime($date), $times);
}

function ac_date_sec2hms($sec, $padHours = false) {

// start with a blank string
    $hms = "";

// do the hours first: there are 3600 seconds in an hour, so if we divide
// the total number of seconds by 3600 and throw away the remainder, we're
// left with the number of hours in those seconds
    $hours = intval(intval($sec) / 3600);

// add hours to $hms (with a leading 0 if asked for)
    $hms .= ( $padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";

// dividing the total seconds by 60 will give us the number of minutes
// in total, but we're interested in *minutes past the hour* and to get
// this, we have to divide by 60 again and then use the remainder
    $minutes = intval(($sec / 60) % 60);

// add minutes to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";

// seconds past the minute are found by dividing the total number of seconds
// by 60 and using the remainder
    $seconds = intval($sec % 60);

// add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

// done!
    return $hms;
}

####################
## ac_crypto
####################

/**
 * Encrypts an string using MCRYPT_RIJNDAEL_256 and MCRYPT_MODE_ECB
 * 
 * @param string $text The RAW string
 * @param string $key The key with which the data will be encrypted.
 * @return string|false The encrypted and base64-safe-encoded string (safe for urls)
 */
function ac_crypto_encrypt($text, $key = null) {
    if (empty($text)) {
        return false;
    }
    if (empty($key))
        $key = ac_config("salt");

    return ac_base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))), true);
}

/**
 * Decrypts an string previously encrypted using ac_crypto_encrypt
 * 
 * @param string $encrypted The RAW encrypted string
 * @param string $salt The key with which the data was encrypted.
 * @return string|false The decrypted string 
 */
function ac_crypto_decrypt($encrypted, $key = null) {
    if (empty($encrypted)) {
        return false;
    }
    if (empty($key))
        $key = ac_config("salt");
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), ac_base64_decode($encrypted, true), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
}

####################
## ac_base64
####################

/**
 * Returns an encoded string, safe for URLs
 * @param string $string
 * @param bool $urlSafe
 * @return string 
 */
function ac_base64_encode($string, $urlSafe = false) {
    $data = base64_encode($string);
    if ($urlSafe) {
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
function ac_base64_decode($string, $urlSafe = false) {
    if ($urlSafe) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
    } else {
        $data = $string;
    }
    return base64_decode($data);
}

####################
## ac_dir
####################

/**
 * Get the directory size
 * @param directory $directory
 * @return integer
 */
function ac_dir_size($directory) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $size+=$file->getSize();
    }
    return $size;
}

/**
 * Removes a directory recursively
 * @param string $dir 
 */
function ac_dir_rm($dir) {
    $files = glob($dir . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (substr($file, -1) == '/')
            ac_dir_rm($file);
        else
            unlink($file);
    }
    if (is_dir($dir))
        rmdir($dir);
}

/**
 * Copy a file, or recursively copy a folder and its contents
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       string   $permissions New folder creation permissions
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function ac_dir_copy($source, $dest, $permissions = 0775) {
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        copy_recursive("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

/**
 * Search for files in a folder recursively
 * @param string $folderPath The folder path
 * @return array The full paths of the files
 */
function ac_dir_files($folder_path) {
    $files = array();
    if (is_dir($folder_path)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder_path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $key => $value) {
            $file_path = realpath($key);
            if (is_file($file_path))
                $files[] = $file_path;
        }
    }
    return $files;
}

/**
 * Return the children folder names from the given path
 * @param string $folder_path
 * @return array 
 */
function ac_dir_folders($folder_path) {
    $folders = array();
    if (is_dir($folder_path)) {
        $iterator = scandir($folder_path);
        foreach ($iterator as $f) {
            if (is_dir($folder_path . $f) && ($f != ".") && ($f != "..")) {
                $folders[] = $f;
            }
        }
    }
    return $folders;
}

####################
## ac_file
####################

/**
 * Joins different files into a single one
 * @param array $source_files Array of file paths
 * @param string $destination_file Destination file path
 * @param string $separator Separator text. Default: line break
 * @param array $vars Variables to expose
 */
function ac_file_join($source_files, $destination_file, $separator = "\n", $vars = array()) {
    ob_start();
    extract($vars);
    foreach ($source_files as $f) {
        if (is_readable($f)) {
            include $f;
            echo $separator;
        }
    }
    $data = ob_get_clean();
    file_put_contents($destination_file, $data);
}

function ac_file_mimetype($file) {
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
    }

    if (!$type || $type == 'application/octet-stream') {
        $secondOpinion = exec('file -b --mime-type ' . escapeshellarg($file), $foo, $returnCode);
        if (($returnCode == '0') && $secondOpinion) {
            $type = $secondOpinion;
        }
    }

    return $type;
}

function ac_file_extension($filename) {
    return ac_arr_last(explode(".", $filename));
}

####################
## ac_download
####################

function ac_download_from_url($url, $destinationFile, $method = 'GET') {
    if (ob_get_length()) {
        ob_end_clean();
    }
    if (ac_is_url($url)) {
        $binaryData = file_get_contents($url, null, stream_context_create(array(
                    'http' => array(
                        'method' => $method,
                        'header' => "Referer: http://" . $_SERVER['SERVER_NAME'] . "/\r\n"
                    )
                )));
        $fname = basename($url);
        if (file_put_contents($destinationFile . $fname, $binaryData))
            return $destinationFile . $fname;
        else
            return false;
    }else {
        return false;
    }
}

function ac_download_file($file, $contentType = null, $rename = null) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    if (!is_readable($file)) {
        return false;
    } else {
        $contentType = !(empty($contentType)) ? $contentType : file_mimetype($file);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename=' . ($rename ? str_replace(" ", "_", trim($rename, " \n\r\t")) : basename($file)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}

function ac_download_content($binaryContent, $filename = "untitled", $contentType = "application/octet-stream") {
    if (ob_get_length()) {
        ob_end_clean();
    }
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . strlen($binaryContent));
    ob_clean();
    flush();
    echo $binaryContent;
    exit;
}

####################
## ac_reflection
####################

/**
 * Retrieves a class constant using reflection
 * @param string $class_name
 * @param string $constant_name
 * @return mixed
 */
function ac_reflection_class_constant($class_name, $constant_name) {
    $reflect = new ReflectionClass($class_name);
    $constants = $reflect->getConstants();

    return $constants[$constant_name];
}

/**
 * Retrieves class constants using reflection
 * @param string $class_name
 * @return mixed
 */
function ac_reflection_class_constants($class_name) {
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
function ac_reflection_static_property($property, $class_name) {
    if (defined($class_name . '::' . $property)) {
        return eval("return {$class_name}::{$property};");
    } elseif (property_exists($class_name, $property)) {
        return eval("return {$class_name}::\${$property};");
    } else {
        return null;
    }
}

####################
## ac_is
####################

function ac_is_empty($var) {
    return empty($var);
}

function ac_is_html($str) {
    return (preg_match('/<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?' . '>/i', $str) > 0);
}

function ac_is_msword($str) {
    return preg_replace('/class="?Mso|style="[^"]*\bmso-|w:WordDocument/i');
}

function ac_is_url($str) {
    return filter_var($str, FILTER_VALIDATE_URL) !== false;
}

function ac_is_email($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
}

function ac_is_webfile($str) {
    $exts = 'xml|json|less|css|js|j|jpg|png|apng|gif|swf|svg|svgz|otf|eot|woff|' .
            'ttf|avi|mp3|mp4|mpg|mov|mpeg|mkv|ogg|ogv|oga|aac|wmv|wma|rm|' .
            'webm|webp|pdf|zip|gz|tar|rar|7z';
    return (preg_match("/\.({$exts})$/", $str) != false);
}

####################
## ac_html
####################

/**
 * Marks each found word in a string with a html5 'mark' tag
 * @param string $string String to search in (plain text only)
 * @param array|string $words Word(s) to mark
 * @return string 
 */
function ac_html_mark($string, $words) {
    if (!is_array($words))
        $words = array($words);
    foreach ($words as $word) {
        $string = str_ireplace($word, '<mark>' . $word . '</mark>', strip_tags($string));
    }
    return $string;
}

function ac_html_trim_br($str) {
    if (!is_string($str))
        return $str;

    $str = trim($str, " \n\r\t");

    $replaced = true;

    while ($replaced == true) {
        $replaced = false;

        if ((mb_strtolower(substr($str, 0, 6)) == "<br />")) {
            $str = substr($str, 6);
            $replaced = true;
        }

        if ((mb_strtolower(substr($str, 0, 5)) == "<br/>")) {
            $str = substr($str, 5);
            $replaced = true;
        }

        if ((mb_strtolower(substr($str, 0, 4)) == "<br>")) {
            $str = substr($str, 4);
            $replaced = true;
        }

        if ((mb_strtolower(substr($str, -6)) == "<br />")) {
            $str = substr($str, 0, strlen($str) - 6);
            $replaced = true;
        }

        if ((mb_strtolower(substr($str, -5)) == "<br/>")) {
            $str = substr($str, 0, strlen($str) - 5);
            $replaced = true;
        }

        if ((mb_strtolower(substr($str, -4)) == "<br>")) {
            $str = substr($str, 0, strlen($str) - 4);
            $replaced = true;
        }
        $str = trim($str, " \n\r\t");
    }

    return $str;
}

####################
## other ac_*
####################

function ac_echo($var, $default = null, $return = false) {
    if (empty($var))
        return print_r($default, $return);
    else {
        return print_r($var, $return);
    }
}

function ac_redirect($url, $status = 301, $httpVersion = "1.1") {
    if (strpos(PHP_SAPI, 'cgi') === 0) {
        header("Status: ", $status);
    } else {
        header("HTTP/{$httpVersion}: ", $status);
    }
    header("Location: ", $url);
}

function ac_removecookie($name, $path = null, $domain = null, $secure = null, $httponly = null) {
    return setcookie($name, "", time() - 3600, $path, $domain, $secure, $httponly);
}

function ac_add_include_path($path) {
    return set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function ac_memory_available() {
    return memory_get_limit() - memory_get_usage(true);
}

/**
 * 
 * @return int amount of bytes
 */
function ac_memory_limit() {
    $memory_limit = ini_get("memory_limit");
    if (strpos($memory_limit, "M")) {
        $memory_limit = intval($memory_limit) * 1024 * 1024;
    } elseif (strpos($memory_limit, "K")) {
        $memory_limit = intval($memory_limit) * 1024;
    } else {
        $memory_limit = intval($memory_limit);
    }
    return $memory_limit;
}

/**
 * Returns the results of preg_match_all()
 * @param string $pattern
 * @param string $subject
 * @param int $flags
 * @param int $offset
 * @return array 
 */
function ac_preg_match_results($pattern, $subject, $flags = null, $offset = null) {
    preg_match_all($pattern, $subject, $matchesarray, $flags, $offset);
    if (!empty($matchesarray))
        return array_pop($matchesarray);
    else
        return array();
}

function ac_ie_version() {
    $match = preg_match('/MSIE ([0-9]\.[0-9])/', $_SERVER['HTTP_USER_AGENT'], $reg);
    if ($match == 0)
        return -1;
    else
        return floatval($reg[1]);
}

function ac_ie_classes() {
    $v = intval(ac_ie_version());
    if ($v == -1)
        return "no-ie";
    switch ($v) {
        case 6: {
                return "ie6 ielt11 ielt10 ielt9 ielt8 ielt7";
            }break;
        case 7: {
                return "ie7 ielt11 ielt10 ielt9 ielt8 iegt6";
            }break;
        case 8: {
                return "ie8 ielt11 ielt10 ielt9 iegt6 iegt7";
            }break;
        case 9: {
                return "ie9 ielt11 ielt10 iegt6 iegt7 iegt8";
            }break;
        case 10: {
                return "ie10 ielt11 iegt6 iegt7 iegt8 iegt9";
            }break;
        case 11: {
                return "ie11 iegt6 iegt7 iegt8 iegt9 iegt10";
            }break;
    }
}

####################
## compat functions
####################

/**
 * Lowercase first character.
 *
 * @param string
 * @return string
 */
if (!function_exists("lcfirst")) {

    function lcfirst($str) {
        return strlen($str) ? strtolower($str[0]) . substr($str, 1) : "";
    }

}

if (!function_exists("array_replace")) :

    /**
     * (PHP 5 &gt;= 5.3.0)<br/>
     * Replaces elements from passed arrays into the first array
     * @link http://php.net/manual/en/function.array-replace.php
     * @param array $array <p>
     * The array in which elements are replaced.
     * </p>
     * @param array $array1 <p>
     * The array from which elements will be extracted.
     * </p>
     * @param array $_ [optional] <p>
     * More arrays from which elements will be extracted.
     * Values from later arrays overwrite the previous values.
     * </p>
     * @return array an array, or null if an error occurs.
     */
    function array_replaces(/* & (?) */$target/* , $from, $from2, ... */) {
        $merge = func_get_args();
        array_shift($merge);
        foreach ($merge as $add) {
            foreach ($add as $i => $v) {
                $target[$i] = $v;
            }
        }
        return $target;
    }

endif;

if (!function_exists("sha256")) :

    function sha256($data) {
        return hash("sha256", $data);
    }

endif;

if (!function_exists('hash_hmac')) :

    function hash_hmac($algorithm, $data, $key, $raw_output = false) {
        $blocksize = 64;
        if (strlen($key) > $blocksize)
            $key = pack('H*', $algorithm($key));

        $key = str_pad($key, $blocksize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blocksize);
        $opad = str_repeat(chr(0x5c), $blocksize);
        $hmac = pack('H*', $algorithm(($key ^ $opad) . pack('H*', $algorithm(($key ^ $ipad) . $data))));

        return $raw_output ? $hmac : bin2hex($hmac);
    }

endif;


if (!function_exists('get_called_class')) :

    function get_called_class($bt = false, $l = 1) {
        if (!$bt)
            $bt = @debug_backtrace();
        if (!isset($bt[$l]))
            throw new Exception("Cannot find called class -> stack level too deep.");
        if (!isset($bt[$l]['type'])) {
            throw new Exception('type not set');
        }
        else
            switch ($bt[$l]['type']) {
                case '::':
                    $lines = file($bt[$l]['file']);
                    $i = 0;
                    $callerLine = '';
                    do {
                        $i++;
                        $callerLine = $lines[$bt[$l]['line'] - $i] . $callerLine;
                    } while (stripos($callerLine, $bt[$l]['function']) === false);
                    preg_match('/([a-zA-Z0-9\_]+)::' . $bt[$l]['function'] . '/', $callerLine, $matches);
                    if (!isset($matches[1])) {
                        // must be an edge case.
                        throw new Exception("Could not find caller class: originating method call is obscured.");
                    }
                    switch ($matches[1]) {
                        case 'self':
                        case 'parent':
                            return get_called_class($bt, $l + 1);
                        default:
                            return $matches[1];
                    }
                // won't get here.
                case '->': switch ($bt[$l]['function']) {
                        case '__get':
                            // edge case -> get class of calling object
                            if (!is_object($bt[$l]['object']))
                                throw new Exception("Edge case fail. __get called on non object.");
                            return get_class($bt[$l]['object']);
                        default: return $bt[$l]['class'];
                    }

                default: throw new Exception("Unknown backtrace method type");
            }
    }


endif;