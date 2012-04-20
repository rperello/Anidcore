<?php
if (!function_exists("add_include_path")) :
    function add_include_path($path) {
        return set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    }
endif;

function echor($var, $default = NULL, $return = false) {
    if (empty($var))
        return print_r($default, $return);
    else {
        return print_r($var, $return);
    }
}

function implode_notempty($glue = ",") {
    $args = func_get_args();
    $glue = $args[0];
    unset($args[0]);
    foreach ($args as $i => $arg) {
        $arg = trim($arg);
        if (($arg == NULL) || ($arg == "") || ($arg == " ")) {
            unset($args[$i]);
        }
    }
    return implode($glue, $args);
}

function is_empty($var) {
    return empty($var);
}

function is_webmedia($str) {
    $exts = 'xml|json|less|css|js|j|jpg|png|apng|gif|swf|svg|svgz|otf|eot|woff|' .
            'ttf|avi|mp3|mp4|mpg|mov|mpeg|mkv|ogg|ogv|oga|aac|wmv|wma|rm|' .
            'webm|webp|pdf|zip|gz|tar|rar|7z';
    return (preg_match("/\.({$exts})$/", $str) != false);
}

function memory_get_avaiable() {
    return memory_get_limit() - memory_get_usage(true);
}

/**
 * 
 * @return int amount of bytes
 */
function memory_get_limit() {
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

function files_join($source_files, $destination_file, $vars = array()) {
    ob_start();
    extract($vars);
    foreach ($source_files as $f) {
        if (is_readable($f)) {
            include $f;
            echo "\n";
        }
    }
    $data = ob_get_clean();
    file_put_contents($destination_file, $data);
}

function ie_version() {
    $match = preg_match('/MSIE ([0-9]\.[0-9])/', $_SERVER['HTTP_USER_AGENT'], $reg);
    if ($match == 0)
        return -1;
    else
        return floatval($reg[1]);
}

function ie_classes() {
    $v = intval(ie_version());
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

/**
 * Returns the results of preg_match_all()
 * @param string $pattern
 * @param string $subject
 * @param int $flags
 * @param int $offset
 * @return array 
 */
function preg_match_results($pattern, $subject, $flags = null, $offset = null) {
    preg_match_all($pattern, $subject, $matchesarray, $flags, $offset);
    if (!empty($matchesarray))
        return array_pop($matchesarray);
    else
        return array();
}

function array_in_array($needle, $haystack) {
    if (!is_array($needle) || !is_array($haystack))
        return false;

    foreach ($haystack as $hv) {
        foreach ($needle as $nv) {
            if ($hv == $nv)
                return true;
        }
    }

    return false;
}

/**
 * Converts cents to currency (with two decimals)
 * @param int $price
 * @param string $dec_point
 * @param string $thousands_sep
 * @return string 
 */
function cents_to_curr($price, $dec_point = ",", $thousands_sep = "") {
    return number_format($price / 100, 2, $dec_point, $thousands_sep);
}

/**
 * Checks if a variable exists inside an array and matches the given php filter or regular expression
 * @param array $arr Associated array of values
 * @param string $key Array key name
 * @param mixed $default Default value if the variable is not set or regexp is false
 * @param mixed $filter FILTER_* constant value or regular expression
 * @return mixed
 */
function array_check_value($arr, $key, $default = false, $filter = NULL) {
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

/**
 * Inject values from supplemental arrays into $target, according to its keys.
 *
 * @param array  $targt
 * @param+ array $supplements
 * @return array
 */
if (!function_exists("array_replace")) {

    function array_replace(/* & (?) */$target/* , $from, $from2, ... */) {
        $merge = func_get_args();
        array_shift($merge);
        foreach ($merge as $add) {
            foreach ($add as $i => $v) {
                $target[$i] = $v;
            }
        }
        return $target;
    }

}

if (!function_exists("sha256")) :
    function sha256($data) {
        return hash("sha256", $data);
    }
endif;

if (!function_exists("removecookie")) :
    function removecookie($name, $path = NULL, $domain = NULL, $secure = NULL, $httponly = NULL) {
        return setcookie($name, "", time() - 3600, $path, $domain, $secure, $httponly);
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

function date_european_to_utc($date_dmy, $hms = "14:00:00") {
    $time = strtotime(preg_replace("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", "\\3-\\2-\\1", $date_dmy) . " " . $hms);
    return date('Y-m-d\\TH:i:s\\.000\\Z', $time - date("Z"));
}

function date_utc($time = NULL) {
    if ($time == NULL)
        $time = time();
    return date('Y-m-d\\TH:i:s\\.000\\Z', $time - date("Z"));
}

function mdy2timestamp($input) {
    $output = false;
    $d = preg_split('#[-/:. ]#', $input);
    if (is_array($d) && count($d) == 3) {
        if (checkdate($d[0], $d[1], $d[2])) {
            $output = "$d[2]-$d[0]-$d[1]";
        }
    }
    return $output;
}

function dmy2timestamp($input) {
    $output = false;
    $d = preg_split('#[-/:. ]#', $input);
    if (is_array($d) && count($d) == 3) {
        if (checkdate($d[1], $d[0], $d[2])) {
            $output = "$d[2]-$d[1]-$d[0]";
        }
    }
    return $output;
}

function timestamp2dmy($timestamp, $separator = "/") {
    return date("Y{$separator}m{$separator}d", strtotime($timestamp));
}

function ftimestamp($timestamp, $format = "d.m.Y") {
    return date($format, strtotime($timestamp));
}

function date_days_in_between($from, $to) {
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

function date_is_in_range($date, $from, $to) {
    $times = date_days_in_between($from, $to);
    return in_array(strtotime($date), $times);
}

function date_sec2hms($sec, $padHours = false) {

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

/**
 * Get the directory size
 * @param directory $directory
 * @return integer
 */
function dir_size($directory) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $size+=$file->getSize();
    }
    return $size;
}

function rmdir_recursive($dir) {
    $files = glob($dir . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (substr($file, -1) == '/')
            rmdir_recursive($file);
        else
            unlink($file);
    }
    if (is_dir($dir))
        rmdir($dir);
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       string   $permissions New folder creation permissions
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copy_recursive($source, $dest, $permissions = 0775) {
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
function dir_files($folder_path) {
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

function dir_folders($folder_path) {
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

function filepath_extension($filepath) {
    $path_info = pathinfo($filepath);
    return $path_info['extension'];
    //return array_last(explode(".",$filepath));
}

function file_extension($filename) {
    return array_last(explode(".", $filename));
}

function download_remote_file($url, $savePath, $port = 80, $method = 'GET') {
    if (ob_get_length())
        ob_end_clean();
// $urlExists = @fsockopen($url, $port);
    if ($url) {
        $binaryData = file_get_contents($url, NULL, stream_context_create(array(
                    'http' => array(
                        'method' => $method,
                        'header' => "Referer: http://" . $_SERVER['SERVER_NAME'] . "/\r\n"
                    )
                )));
        $fname = basename($url);
        if (file_put_contents($savePath . $fname, $binaryData))
            return $savePath . $fname;
        else
            return false;
    }else {
        return false;
    }
}

function download_file($file, $contentType = NULL, $rename = NULL) {
    if (ob_get_length())
        ob_end_clean();
    if (!is_file($file))
        return false;
    else {
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

function download_content($content, $filename = "untitled", $contentType = "application/octet-stream") {
    if (ob_get_length())
        ob_end_clean();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . strlen($content));
    ob_clean();
    flush();
    echo $content;
    exit;
}

function output_file($file) {
    if (ob_get_length())
        ob_end_clean();
    header("Content-type:" . file_mimetype($file));
    readfile($file);
    exit;
}

function file_mimetype($file) {
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
    } else {
        require_once AC_SYSTEM_PATH . 'vendors/upgradephp/ext/mime.php';
        $type = mime_content_type($file);
    }

    if (!$type || $type == 'application/octet-stream') {
        $secondOpinion = exec('file -b --mime-type ' . escapeshellarg($file), $foo, $returnCode);
        if ($returnCode == '0' && $secondOpinion) {
            $type = $secondOpinion;
        }
    }

    if (!$type || $type == 'application/octet-stream') {
        require_once 'upgradephp/ext/mime.php';
        $exifImageType = exif_imagetype($file);
        if ($exifImageType !== false) {
            $type = image_type_to_mime_type($exifImageType);
        }
    }

    return $type;
}

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

function utf8_strtolower($str) {
    return mb_strtolower($str, "UTF-8");
}

function utf8_strtoupper($str) {
    return mb_strtoupper($str, "UTF-8");
}

function is_msword($str) {
    return preg_replace('/class="?Mso|style="[^"]*\bmso-|w:WordDocument/i');
}

function str_latin1_to_utf8($str) {
    return utf8_encode(utf8_decode($str));
}

/*
 * Marks each occurrence of $words in a string with a html5 'mark' tag
 */

/**
 * Marks each found word in a string with a html5 'mark' tag
 * @param string $string String to search in (plain text only)
 * @param array|string $words Word(s) to mark
 * @return string 
 */
function str_mark($string, $words) {
    if (!is_array($words))
        $words = array($words);
    foreach ($words as $word) {
        $string = str_ireplace($word, '<mark>' . $word . '</mark>', strip_tags($string));
    }
    return $string;
}

function is_html($str) {
    return (preg_match('/<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?' . '>/i', $str) > 0);
}

function is_url($str) {
    return filter_var($str, FILTER_VALIDATE_URL) !== false;
}

function is_email($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Returns a camelized string. Detects some word separators by default.
 * @param string $str
 * @param array $separators
 * @param array $replacements
 * @return string
 */
function str_camelize($str, $separators = array('_', '-', '.', "'", ','), $replacements = array(' ', ' ', '', '', '')) {
    $str = str_replace($separators, $replacements, $str);
    $str = trim(implode('', explode(' ', str_ucwords($str))));
    return str_lcfirst($str);
}

function str_reduce($str, $length, $append = "") {
    if (($length > 0) && (strlen($str) > $length)) {
        return substr($str, 0, $length) . $append;
    }else
        return $str;
}

function str_reduce_words($str, $length, $append = "") {
    $str2 = preg_replace('/\s\s+/', ' ', $str);
    $words = explode(" ", $str2);
    if (($length > 0) && (count($words) > $length)) {
        return implode(" ", array_some($words, $length)) . $append;
        //return substr($str, 0, $length).$append;
    }else
        return $str;
}

function br_trim($str) {
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

/**
 * Converts a friendly url-like formatted string to a human readable string.
 * Detects '-' and '_' word separators by default.
 * @param string $str
 * @param array $separators
 * @param array $replacements
 * @return string
 */
function str_humanize($str, $separators = array('_', '-'), $replacements = array(' ', ' ')) {
    $str = str_replace($separators, $replacements, $str);
    return str_ucfirst($str);
}

/**
 * Converts any string to a friendly-url string
 * @param string $str
 * @param string $separator
 * @return string
 */
function str_slug($str, $separator = '-', $allowDots = false, $allowSlashes = false) {
    if ($separator == '|')
        $separator = '-';
    $friendly_url = html_entity_decode($str, NULL, 'UTF-8');

    $special_chars = "á|é|í|ó|ú|Á|É|Í|Ó|Ú|à|è|ì|ò|ù|À|È|Ì|Ò|Ù|ä|ë|ï|ö|ü|Ä|Ë|Ï|Ö|Ü|â|ê|î|ô|û|Â|Ê|Î|Ô|Û|ý|Ý|ç|Ç|ñ|Ñ|€| |_|-";
    if ($allowDots == false)
        $special_chars.="|.";
    $special_chars_array = explode('|', $special_chars);

    $replacement_chars = 'a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|a|e|i|o|u|y|y|c|c|n|n|e|' . $separator . '|' . $separator . '|' . $separator;
    if ($allowDots == false)
        $replacement_chars.="|" . $separator;
    $replacement_chars_array = explode('|', $replacement_chars);

    $friendly_url = str_replace($special_chars_array, $replacement_chars_array, $friendly_url);

    //Remove characters that not matches the regular expression and lowercase the string
    $allow = "";
    if ($allowDots)
        $allow.='\\.';
    if ($allowSlashes)
        $allow.='\/';
    $friendly_url = mb_strtolower(preg_replace('#[^A-Za-z0-9' . $allow . $separator . ']#', '', $friendly_url));
    return trim(str_replace($separator . $separator, $separator, $friendly_url), $separator);
}

/**
 * Converts a slugged string into camel case
 * @param string $str
 * @return string
 */
function str_unslug($str) {
    $str = str_camelize($str, array('_', '-', '.', "'", ','), array(' ', ' ', ' ', '', ''));
    if (is_numeric($str{0}))
        $str = '_' . $str;
    return $str;
}

/**
 * Lowcase the first character of the string.
 * @param string $str
 * @return string
 */
function str_lcfirst($str) {
    //$str = trim($str);
    return (string) (mb_strtolower(substr($str, 0, 1)) . substr($str, 1));
}

/**
 * Upcase the first character of the string.
 * @param string $str
 * @return string
 */
function str_ucfirst($str) {
    //$str = trim($str);
    return (string) (strtoupper(substr($str, 0, 1)) . substr($str, 1));
}

/**
 * First converts all characters into lowercase, then uppercases the first letter of each word.
 * @param string $str
 * @return string
 */
function str_ucwords($str) {
    //$str = trim($str);
    return ucwords(mb_strtolower($str));
}

function str_remove_fontstyles($str) {
    $patterns = array();

    //CSS attributes
    $patterns[] = '/style\=\"[^\"]*(\s*(font-size)\s*:\s*[0-9]{1,}\s*(px|em|pt|%|in|cm|mm|ex|pc)\s*;*\s*)/i';
    $patterns[] = '/(\s*(font-family|font)\s*:\s*[\"\',\.\s\-\_0-9a-zA-Z]{1,}\s*;*\s*)/i';
    $patterns[] = '/(\s*(background|color|background-image|background-color)\s*:\s*[0-9a-zA-Z#\(\),\.%\s\"\'\-\_\/\\\\]{1,}\s*;*\s*)/i';
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
 * Converts a base16 string to a binary string
 * @param string $hexdata
 * @return string
 */
function hex2bin($hexdata) {
    $bindata = "";

    for ($i = 0; $i < strlen($hexdata); $i+=2) {
        $bindata.=chr(hexdec(substr($hexdata, $i, 2)));
    }

    return $bindata;
}