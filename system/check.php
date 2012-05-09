<?php
/**
 * This is a Standalone test file and does not require extra libraries or functions to work,
 * but if Anidcore Framework is detected, an array containing "html" and "passed" values
 * will be returned and no output will be printed.
 */
if (!defined("AC_PATH")) {
    error_reporting(-1);
    ini_set("display_errors", true);
    ini_set("default_charset", "utf8");
} else {
    ob_start();
}

if(!class_exists("Ac_ServerTest")):
    class Ac_ServerTest {

        public $TITLE = "Anidcore Framework - Server compatibility test";
        public $REQUIRED_PHP_VERSION = "5.3.0";
        public $MYSQL_VERSION = null;
        public $ROOT_PATH = null;
        public $TESTS = array();
        public $ERROR_COUNT = 0;
        public $WARNING_COUNT = 0;
        public $IMAGEMAGICK_PATH = null;

        public function __construct() {
            $this->ROOT_PATH = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $tests = array();

            $tests["php.version"] = array(
                "title" => "PHP Version >= " . $this->REQUIRED_PHP_VERSION,
                "passed" => version_compare(PHP_VERSION, $this->REQUIRED_PHP_VERSION, ">="),
                "result" => 'v' . PHP_VERSION,
                "level" => "error"
            );

            $tests["php.safe_mode"] = array(
                "title" => "PHP safe_mode disabled",
                "passed" => (ini_get('safe_mode') == false),
                "level" => "error"
            );

            $test_fs = $this->test_filesystem();
            $tests["php.filesystem"] = array(
                "title" => "PHP can create and remove directories and files",
                "passed" => $test_fs === true,
                "result" => strlen($test_fs) > 1 ? "Errors: " . $test_fs : null,
                "level" => "error"
            );

            $tests["apache"] = array(
                "title" => "HTTP Server is Apache",
                "passed" => ((isset($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')) ? 1 : 0) || (function_exists("apache_get_modules")),
                "level" => "error"
            );

            $tests["apache.mod_rewrite"] = array(
                "title" => "Apache mod_rewrite module is enabled",
                "passed" => (function_exists("apache_get_modules") && in_array("mod_rewrite", apache_get_modules())),
                "level" => "error"
            );

            $this->findMysqlVersion();

            $tests["mysql.version"] = array(
                "title" => "MySQL Version >= 5",
                "passed" => (preg_match('/^5/', $this->MYSQL_VERSION)),
                "result" => 'v' . $this->MYSQL_VERSION,
                "level" => "error"
            );


            //PHP Extensions:
            $tests["php.ext.spl"] = array(
                "title" => "Standard PHP Library (spl)",
                "passed" => function_exists("spl_autoload_register") && class_exists("ArrayObject"),
                "level" => "error"
            );
            $tests["php.ext.tokenizer"] = array(
                "title" => "PHP Code Tokenizer (tokenizer)",
                "passed" => extension_loaded('tokenizer'),
                "level" => "error"
            );
            $tests["php.ext.reflection"] = array(
                "title" => "PHP Reflection extension (Reflection)",
                "passed" => extension_loaded('Reflection'),
                "level" => "error"
            );
            $tests["php.ext.session"] = array(
                "title" => "PHP Session handler extension (session)",
                "passed" => extension_loaded('session'),
                "level" => "error"
            );
            $tests["php.ext.openssl"] = array(
                "title" => "openssl extension for SSL connections",
                "passed" => extension_loaded('openssl'),
                "level" => "error"
            );
            $tests["php.ext.pcre"] = array(
                "title" => "Perl Common Regular Expressions (pcre)",
                "passed" => extension_loaded('pcre'),
                "level" => "error"
            );
            $tests["php.ext.filter"] = array(
                "title" => "Filter extension (filter)",
                "passed" => extension_loaded('filter'),
                "level" => "error"
            );
            $tests["php.ext.mcrypt"] = array(
                "title" => "Encrypt / Decrypt functions (mcrypt)",
                "passed" => extension_loaded('mcrypt'),
                "level" => "error"
            );
            $tests["php.ext.hash"] = array(
                "title" => "Hashing functions (hash)",
                "passed" => extension_loaded('hash'),
                "level" => "error"
            );
            $tests["php.ext.json"] = array(
                "title" => "JSON Parser (json)",
                "passed" => extension_loaded('json'),
                "level" => "error"
            );
            $tests["php.ext.dom"] = array(
                "title" => "XML Parser (dom)",
                "passed" => extension_loaded('dom'),
                "level" => "warn"
            );
            $tests["php.ext.simplexml"] = array(
                "title" => "XML Parser (simplexml)",
                "passed" => extension_loaded('simplexml'),
                "level" => "warn"
            );
            $tests["php.ext.mbstring"] = array(
                "title" => "Multi-byte string functions (mbstring)",
                "passed" => extension_loaded('mbstring'),
                "level" => "warn"
            );
            $tests["php.ext.gettext"] = array(
                "title" => "gettext extension",
                "passed" => extension_loaded('gettext'),
                "level" => "warn"
            );
            $tests["php.ext.iconv"] = array(
                "title" => "iconv extension for character encoding conversions",
                "passed" => extension_loaded('iconv'),
                "level" => "error"
            );

            $tests["php.ext.pdo"] = array(
                "title" => "PDO Database manipulation extension",
                "passed" => extension_loaded('pdo'),
                "level" => "error"
            );

            $tests["php.ext.pdo_mysql"] = array(
                "title" => "pdo_mysql extension",
                "passed" => extension_loaded('pdo_mysql'),
                "level" => "warn"
            );

            $tests["php.ext.pdo_sqlite"] = array(
                "title" => "pdo_sqlite extension",
                "passed" => extension_loaded('pdo_sqlite'),
                "level" => "warn"
            );

            $tests["php.ext.gd"] = array(
                "title" => "GD Graphics Library for Image manipulation (gd)",
                "passed" => extension_loaded('gd'),
                "level" => "warn"
            );
            if ($tests["php.ext.gd"]["passed"]) {
                $gdinfo = gd_info();
                $tests["php.ext.gd2"] = array(
                    "title" => "GD Version >= 2",
                    "passed" => preg_match("/^2\./", trim($gdinfo["GD Version"], " ()")) !== false,
                    "result" => $gdinfo["GD Version"],
                    "level" => "warn"
                );
            } else {
                $tests["php.ext.gd2"] = array(
                    "title" => "GD Version >= 2",
                    "passed" => false,
                    "level" => "warn"
                );
            }

            $imagemagick = $this->test_imagemagick();
            $tests["php.ext.imagemagick"] = array(
                "title" => "ImageMagick is installed on server",
                "passed" => !empty($imagemagick["version"]),
                "level" => (!$tests["php.ext.gd2"]["passed"]) ? "warn" : "info"
            );

            if ($tests["php.ext.imagemagick"]["passed"]) {
                $tests["php.ext.imagemagick"]["result"] = "v" . $imagemagick["version"] . " @ " . $imagemagick["path"];
            }

            $tests["php.ext.imagick"] = array(
                "title" => "Imagick PHP Extension for ImageMagick",
                "passed" => extension_loaded('imagemagick'),
                "level" => "info"
            );

            $tests["php.ext.magicwand"] = array(
                "title" => "Magicwand PHP Extension for ImageMagick",
                "passed" => extension_loaded('magicwand'),
                "level" => "info"
            );

            $tests["php.ext.exif"] = array(
                "title" => "EXIF Extension for Image metadata",
                "passed" => extension_loaded('exif'),
                "level" => "info"
            );

            $tests["php.ext.fileinfo"] = array(
                "title" => "fileinfo extension",
                "passed" => extension_loaded('fileinfo'),
                "level" => "info"
            );

            $tests["php.ext.curl"] = array(
                "title" => "CURL extension (curl)",
                "passed" => extension_loaded('curl'),
                "level" => "warn"
            );

            $this->test_protocolExtensions($tests);
            $this->test_cacheExtensions($tests);
            $this->test_archiverExtensions($tests);

            $this->TESTS = $tests;
            $this->findErrors();
        }

        protected function test_filesystem() {
            $dir = $this->ROOT_PATH . "app" . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "test" . DIRECTORY_SEPARATOR;
            $err = "";
            ob_start();
            try {
                if (is_dir($dir) || is_readable($dir . "test.txt")) {
                    return false;
                }

                $err = "mkdir";
                mkdir($dir, 0755, true);

                $err = "file_put_contents";
                file_put_contents($dir . "test.txt", "TEST");

                $err = "is_readable";
                if (is_readable($dir . "test.txt")) {
                    $err = "file_get_contents";
                    $testfile = file_get_contents($dir . "test.txt");

                    if ($testfile != "TEST")
                        return "content-mismatch";
//                else return true;
                    $err = "unlink";
                    if (unlink($dir . "test.txt")) {
                        $err = "rmdir";
                        if (rmdir($dir)) {
                            $err = true;
                        }
                    }else
                        $err = "unlink";
                }
                $ob = ob_get_clean();
                if (!empty($ob))
                    $err = "<br>" . $ob;

                return $err;
            } catch (Exception $exc) {
                return $err;
            }
        }

        protected function test_imagemagick() {
            $version = @shell_exec("convert --version");
            $path = null;

            if (empty($version))
                $version = @shell_exec("/usr/bin/convert --version");
            else
                $path = "convert";

            if (empty($version))
                $version = @shell_exec("/usr/sbin/convert --version");
            elseif (empty($path))
                $path = "/usr/bin/convert";

            if (empty($version))
                $version = @shell_exec("/opt/local/bin/convert --version");
            elseif (empty($path))
                $path = "/usr/sbin/convert";

            if (empty($version))
                $version = @shell_exec("/usr/local/imagemagick/bin/convert --version");
            elseif (empty($path))
                $path = "/opt/local/bin/convert";

            if (!empty($version)) {
                if (empty($path))
                    $path = "/usr/local/imagemagick/bin/convert";
                if (preg_match("/ImageMagick ([0-9\.\-]{1,})/i", $version, $matches)) {
                    $version = $matches[1];
                }else
                    $version = null;
            }else
                $version = null;

            $this->IMAGEMAGICK_PATH = $path;
            return array("version" => $version, "path" => $path);
        }

        protected function test_protocolExtensions(&$tests) {
            $tests["php.ext.soap"] = array(
                "title" => "SOAP Webservices extension (soap)",
                "passed" => extension_loaded('soap'),
                "level" => "info"
            );
            $tests["php.ext.ftp"] = array(
                "title" => "FTP Extension",
                "passed" => extension_loaded('ftp'),
                "level" => "info"
            );
            $tests["php.ext.imap"] = array(
                "title" => "IMAP Extension",
                "passed" => extension_loaded('imap'),
                "level" => "info"
            );
            $tests["php.ext.smtp"] = array(
                "title" => "SMTP Extension",
                "passed" => extension_loaded('smtp'),
                "level" => "info"
            );
            $tests["php.ext.ldap"] = array(
                "title" => "LDAP Extension",
                "passed" => extension_loaded('ldap'),
                "level" => "info"
            );
        }

        protected function test_cacheExtensions(&$tests) {
            $tests["php.ext.memcache"] = array(
                "title" => "Memcache Cache Extension",
                "passed" => extension_loaded('memcache'),
                "level" => "warn"
            );

            $tests["php.ext.apc"] = array(
                "title" => "APC Cache Extension",
                "passed" => extension_loaded('apc'),
                "level" => "warn"
            );

            $tests["php.ext.xcache"] = array(
                "title" => "XCache Cache Extension",
                "passed" => extension_loaded('xcache'),
                "level" => "warn"
            );
        }

        protected function test_archiverExtensions(&$tests) {
            $tests["php.ext.bz2"] = array(
                "title" => "BZip2 Archiver Extension",
                "passed" => extension_loaded('bz2'),
                "level" => "info"
            );
            $tests["php.ext.yaz"] = array(
                "title" => "YAZ Archiver Extension",
                "passed" => extension_loaded('yaz'),
                "level" => "info"
            );
            $tests["php.ext.zip"] = array(
                "title" => "ZIP Archiver Extension",
                "passed" => extension_loaded('zip'),
                "level" => "info"
            );
            $tests["php.ext.zlib"] = array(
                "title" => "ZLib Archiver Extension",
                "passed" => extension_loaded('zlib'),
                "level" => "info"
            );
        }

        protected function findMysqlVersion() {
            if (empty($this->MYSQL_VERSION)) {
                ob_start();
                phpinfo();
                $phpinfo = ob_get_contents();
                ob_end_clean();

                $start = explode("<h2><a name=\"module_mysql\">mysql</a></h2>", $phpinfo, 1000);
                if (count($start) < 2) {
                    $this->MYSQL_VERSION = 0;
                } else {
                    $again = explode("<tr><td class=\"e\">Client API version </td><td class=\"v\">", $start[1], 1000);
                    $last_time = explode(" </td></tr>", $again[1], 1000);
                    $this->MYSQL_VERSION = $last_time[0];
                }
            }
            return $this->MYSQL_VERSION;
        }

        protected function findErrors() {
            foreach ($this->TESTS as $test) {
                if (!$test["passed"]) {
                    if ($test["level"] == "error")
                        $this->ERROR_COUNT++;
                    elseif ($test["level"] == "warn")
                        $this->WARNING_COUNT++;
                }
            }
        }

        public function isSuccess() {
            return $this->ERROR_COUNT == 0;
        }

    }
endif;
$servertest = new Ac_ServerTest();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $servertest->TITLE; ?></title>
        <meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <meta name="robots" content="NOINDEX,NOFOLLOW" />
        <meta name="googlebot" content="noindex,nofollow" />

        <meta name="author" content="Copyright 2012, ANID Internet Estudio, www.anid.es" />
        <link type="text/css" href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" />
    </head>
    <body>
        <div id="wrapper" class="container" style="margin:20px auto;">
            <h1 style="margin:0 0 20px 0;"><?php echo $servertest->TITLE; ?> <br />
                <small>TEST RESULT: <?php 
                if($servertest->isSuccess()) {
                    echo '<span style="font-size:14px" class="label label-success">PASSED';
                    //print_r($test);
                    if($servertest->WARNING_COUNT > 0) echo ' (with '.$servertest->WARNING_COUNT." warnings)";
                    echo '</span>';
                }else{
                    echo '<span style="font-size:14px"  class="label label-important">NOT PASSED: '.$servertest->ERROR_COUNT.' ERRORS';
                    if($servertest->WARNING_COUNT > 0) echo ' (and '.$servertest->WARNING_COUNT." warnings)";
                    echo ' FOUND</span>';
                }?> </small> </h1>
            
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Test name</th>
                        <th style="width:200px">Result</th>
                    </tr>
                </thead>
                <tbody>
            <?php
            
            foreach($servertest->TESTS as $key => $test){
                ?>
                    <tr>
                        <td><?php echo $test["title"] ?></td>
                        <td><?php 
                        
                        if($test["passed"]){
                            echo '<span class="label label-success">Ok</span>';
                        }else{
                            if(preg_match("/^php\.ext\./",$key)){
                                switch($test["level"]){
                                    case 'info': echo '<span class="label">Not installed</span>'; break;
                                    case 'warn': echo '<span class="label label-warning">Not installed</span>'; break;
                                    default: echo '<span class="label label-important">Not installed</span>'; break;
                                }
                            }else{
                                switch($test["level"]){
                                    case 'info': echo '<span class="label">Not passed</span>'; break;
                                    case 'warn': echo '<span class="label label-warning">Not passed</span>'; break;
                                    default: echo '<span class="label label-important">Not passed</span>'; break;
                                }
                            }
                        }
                        if(isset($test["result"]) && !empty($test["result"])) echo ' <code style="color:#444">'.$test["result"].'</code><br />';
                        
                        ?></td>
                    </tr>
               <?php
            }
            
            ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2"><br /></th>
                    </tr>
                    <tr>
                        <th>PHP SAPI:</th>
                        <td><?php echo php_sapi_name(); ?></td>
                    </tr>
                    <tr>
                        <th>Current PHP script owner:</th>
                        <td><?php echo "Name=".get_current_user(). '; ID='.getmyuid().'; Group='. getmygid() ; ?></td>
                    </tr>
                    <tr>
                        <th>Current PHP process owner:</th>
                        <td><?php 
                        $processUser = posix_getpwuid(posix_geteuid());
                        echo "Name=".$processUser["name"]. '; ID='.$processUser["uid"].'; Group='. $processUser["gid"]; 
                        ?></td>
                    </tr>
                    <tr>
                        <th colspan="2"><br />Loaded PHP extensions:</th>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo implode(", ", get_loaded_extensions()); ?></td>
                    </tr>
                    <?php if(function_exists("apache_get_modules")): ?>
                    <tr>
                        <th colspan="2"><br />Loaded Apache modules:</th>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo implode(", ", apache_get_modules()); ?></td>
                    </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
            <!--{CUSTOM_CODE}-->
        </div>
    </body>
</html>
<?php
if (defined("AC_PATH"))
    return array("html" => ob_get_clean(), "passed"=>$servertest->isSuccess());
?>