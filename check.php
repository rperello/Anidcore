<?php
error_reporting(-1);
ini_set("display_errors",true);
ini_set("default_charset","utf8");

class ServerTest{
    public $TITLE = "Rino Framework - Server compatibility test";
    public $REQUIRED_PHP_VERSION = "5.2.6";
    public $MYSQL_VERSION = null;
    public $ROOT_PATH = null;
    public $TESTS=array();
    public $ERROR_COUNT=0;
    public $WARNING_COUNT=0;
    public $IMAGEMAGICK_PATH=null;
    
    public function __construct() {
        $this->ROOT_PATH=realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $tests = array();
        
        $tests["php.version"]=array(
            "title"=>"PHP Version >= ".$this->REQUIRED_PHP_VERSION,
            "passed"=>version_compare(PHP_VERSION, $this->REQUIRED_PHP_VERSION, ">="),
            "result"=>'v'.PHP_VERSION,
            "level"=>"error"
        );
        
        $tests["php.safe_mode"]=array(
            "title"=>"PHP safe_mode disabled",
            "passed"=>(ini_get('safe_mode')==false),
            "level"=>"error"
        );
        
        $test_fs=$this->test_filesystem();
        $tests["php.filesystem"]=array(
            "title"=>"PHP can create and remove directories and files",
            "passed"=>$test_fs===true,
            "result"=>strlen($test_fs) > 1 ? "Errors: ".$test_fs : null,
            "level"=>"error"
        );
        
        $tests["apache"]=array(
            "title"=>"HTTP Server is Apache",
            "passed"=>((isset($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')) ? 1 : 0) || (function_exists("apache_get_modules")),
            "level"=>"error"
        );
        
        $tests["apache.mod_rewrite"]=array(
            "title"=>"Apache mod_rewrite module is enabled",
            "passed"=>(function_exists("apache_get_modules") && in_array("mod_rewrite", apache_get_modules())),
            "level"=>"error"
        );
        
        $this->findMysqlVersion();
        
        $tests["mysql.version"]=array(
            "title"=>"MySQL Version >= 5",
            "passed"=>(preg_match('/^5/', $this->MYSQL_VERSION)),
            "result"=>'v'.$this->MYSQL_VERSION,
            "level"=>"error"
        );
        
        
        //PHP Extensions:
        $tests["php.ext.spl"]=array(
            "title"=>"Standard PHP Library (spl)",
            "passed"=>function_exists("spl_autoload_register") && class_exists("ArrayObject"),
            "level"=>"error"
        );
        $tests["php.ext.tokenizer"]=array(
            "title"=>"PHP Code Tokenizer (tokenizer)",
            "passed"=>extension_loaded('tokenizer'),
            "level"=>"error"
        );
        $tests["php.ext.reflection"]=array(
            "title"=>"PHP Reflection extension (Reflection)",
            "passed"=>extension_loaded('Reflection'),
            "level"=>"error"
        );
        $tests["php.ext.session"]=array(
            "title"=>"PHP Session handler extension (session)",
            "passed"=>extension_loaded('session'),
            "level"=>"error"
        );
        $tests["php.ext.openssl"]=array(
            "title"=>"openssl extension for SSL connections",
            "passed"=>extension_loaded('openssl'),
            "level"=>"error"
        );
        $tests["php.ext.pcre"]=array(
            "title"=>"Perl Common Regular Expressions (pcre)",
            "passed"=>extension_loaded('pcre'),
            "level"=>"error"
        );
        $tests["php.ext.filter"]=array(
            "title"=>"Filter extension (filter)",
            "passed"=>extension_loaded('filter'),
            "level"=>"error"
        );
        $tests["php.ext.mcrypt"]=array(
            "title"=>"Encrypt / Decrypt functions (mcrypt)",
            "passed"=>extension_loaded('mcrypt'),
            "level"=>"error"
        );
        $tests["php.ext.hash"]=array(
            "title"=>"Hashing functions (hash)",
            "passed"=>extension_loaded('hash'),
            "level"=>"error"
        );
        $tests["php.ext.json"]=array(
            "title"=>"JSON Parser (json)",
            "passed"=>extension_loaded('json'),
            "level"=>"error"
        );
        $tests["php.ext.dom"]=array(
            "title"=>"XML Parser (dom)",
            "passed"=>extension_loaded('dom'),
            "level"=>"warn"
        );
        $tests["php.ext.simplexml"]=array(
            "title"=>"XML Parser (simplexml)",
            "passed"=>extension_loaded('simplexml'),
            "level"=>"warn"
        );
        $tests["php.ext.mbstring"]=array(
            "title"=>"Multi-byte string functions (mbstring)",
            "passed"=>extension_loaded('mbstring'),
            "level"=>"warn"
        );
        $tests["php.ext.gettext"]=array(
            "title"=>"gettext extension",
            "passed"=>extension_loaded('gettext'),
            "level"=>"warn"
        );
        $tests["php.ext.iconv"]=array(
            "title"=>"iconv extension for character encoding conversions",
            "passed"=>extension_loaded('iconv'),
            "level"=>"error"
        );
        
        $tests["php.ext.pdo"]=array(
            "title"=>"PDO Database manipulation extension",
            "passed"=>extension_loaded('pdo'),
            "level"=>"error"
        );
        
        $tests["php.ext.pdo_mysql"]=array(
            "title"=>"pdo_mysql extension",
            "passed"=>extension_loaded('pdo_mysql'),
            "level"=>"warn"
        );
        
        $tests["php.ext.pdo_sqlite"]=array(
            "title"=>"pdo_sqlite extension",
            "passed"=>extension_loaded('pdo_sqlite'),
            "level"=>"warn"
        );
        
        $tests["php.ext.gd"]=array(
            "title"=>"GD Graphics Library for Image manipulation (gd)",
            "passed"=>extension_loaded('gd'),
            "level"=>"warn"
        );
        if($tests["php.ext.gd"]["passed"]){
            $gdinfo = gd_info();
            $tests["php.ext.gd2"]=array(
                "title"=>"GD Version >= 2",
                "passed"=>preg_match("/^2\./",trim($gdinfo["GD Version"], " ()"))!==false,
                "result"=>$gdinfo["GD Version"],
                "level"=>"warn"
            );
        }else{
            $tests["php.ext.gd2"]=array(
                "title"=>"GD Version >= 2",
                "passed"=>false,
                "level"=>"warn"
            );
        }
        
        $imagemagick = $this->test_imagemagick();
        $tests["php.ext.imagemagick"]=array(
            "title"=>"ImageMagick is installed on server",
            "passed"=>!empty($imagemagick["version"]),
            "level"=>(!$tests["php.ext.gd2"]["passed"]) ? "warn" : "info"
        );
        
        if($tests["php.ext.imagemagick"]["passed"]){
            $tests["php.ext.imagemagick"]["result"]="v".$imagemagick["version"]." @ ".$imagemagick["path"];
        }
        
        $tests["php.ext.imagick"]=array(
            "title"=>"Imagick PHP Extension for ImageMagick",
            "passed"=>extension_loaded('imagemagick'),
            "level"=>"info"
        );
        
        $tests["php.ext.magicwand"]=array(
            "title"=>"Magicwand PHP Extension for ImageMagick",
            "passed"=>extension_loaded('magicwand'),
            "level"=>"info"
        );
        
        $tests["php.ext.exif"]=array(
            "title"=>"EXIF Extension for Image metadata",
            "passed"=>extension_loaded('exif'),
            "level"=>"info"
        );
        
        $tests["php.ext.fileinfo"]=array(
            "title"=>"fileinfo extension",
            "passed"=>extension_loaded('fileinfo'),
            "level"=>"info"
        );
  
        $tests["php.ext.curl"]=array(
            "title"=>"CURL extension (curl)",
            "passed"=>extension_loaded('curl'),
            "level"=>"warn"
        );
        
        $this->test_protocolExtensions($tests);
        $this->test_cacheExtensions($tests);
        $this->test_archiverExtensions($tests);
        
        $this->TESTS = $tests;
        $this->findErrors();
    }
    
    protected function test_filesystem(){
        $dir = $this->ROOT_PATH."app".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."test".DIRECTORY_SEPARATOR;
        $err = "";
        ob_start();
        try {
            if(is_dir($dir) || is_readable($dir."test.txt")){
                return false;
            }
            
            $err = "mkdir";
            mkdir($dir, 0755, true);
            
            $err = "file_put_contents";
            file_put_contents($dir."test.txt", "TEST");
            
            $err = "is_readable";
            if(is_readable($dir."test.txt")){
                $err = "file_get_contents";
                $testfile = file_get_contents($dir."test.txt");
                
                if($testfile!="TEST") return "content-mismatch";
//                else return true;
                $err = "unlink";
                if(unlink($dir."test.txt")){
                    $err = "rmdir";
                    if(rmdir($dir)){
                        $err = true;
                    }
                }else $err = "unlink";
            }
            $ob = ob_get_clean();
            if(!empty($ob)) $err="<br>".$ob;
            
            return $err;
        } catch (Exception $exc) {
            return $err;
        }
    }
    
    protected function test_imagemagick(){
        $version = @shell_exec("convert --version");
        $path = null;
        
        if(empty($version)) $version = @shell_exec("/usr/bin/convert --version");
        else $path = "convert";
        
        if(empty($version)) $version = @shell_exec("/usr/sbin/convert --version");
        elseif(empty($path)) $path = "/usr/bin/convert";
        
        if(empty($version)) $version = @shell_exec("/opt/local/bin/convert --version");
        elseif(empty($path)) $path = "/usr/sbin/convert";
        
        if(empty($version)) $version = @shell_exec("/usr/local/imagemagick/bin/convert --version");
        elseif(empty($path)) $path = "/opt/local/bin/convert";
        
        if(!empty($version)){
            if(empty($path)) $path = "/usr/local/imagemagick/bin/convert";
            if(preg_match("/ImageMagick ([0-9\.\-]{1,})/i", $version, $matches)){
                $version = $matches[1];
            }else $version=null;
        }else $version=null;
        
        $this->IMAGEMAGICK_PATH = $path;
        return array("version"=>$version, "path"=>$path);
    }
    
    protected function test_protocolExtensions(&$tests){
        $tests["php.ext.soap"]=array(
            "title"=>"SOAP Webservices extension (soap)",
            "passed"=>extension_loaded('soap'),
            "level"=>"info"
        );
        $tests["php.ext.ftp"]=array(
            "title"=>"FTP Extension",
            "passed"=>extension_loaded('ftp'),
            "level"=>"info"
        );
        $tests["php.ext.imap"]=array(
            "title"=>"IMAP Extension",
            "passed"=>extension_loaded('imap'),
            "level"=>"info"
        );
        $tests["php.ext.smtp"]=array(
            "title"=>"SMTP Extension",
            "passed"=>extension_loaded('smtp'),
            "level"=>"info"
        );
        $tests["php.ext.ldap"]=array(
            "title"=>"LDAP Extension",
            "passed"=>extension_loaded('ldap'),
            "level"=>"info"
        );
    }
    
    protected function test_cacheExtensions(&$tests){
        $tests["php.ext.memcache"]=array(
            "title"=>"Memcache Cache Extension",
            "passed"=>extension_loaded('memcache'),
            "level"=>"warn"
        );
        
        $tests["php.ext.apc"]=array(
            "title"=>"APC Cache Extension",
            "passed"=>extension_loaded('apc'),
            "level"=>"warn"
        );
        
        $tests["php.ext.xcache"]=array(
            "title"=>"XCache Cache Extension",
            "passed"=>extension_loaded('xcache'),
            "level"=>"warn"
        );
    }
    
    protected function test_archiverExtensions(&$tests){
        $tests["php.ext.bz2"]=array(
            "title"=>"BZip2 Archiver Extension",
            "passed"=>extension_loaded('bz2'),
            "level"=>"info"
        );
        $tests["php.ext.yaz"]=array(
            "title"=>"YAZ Archiver Extension",
            "passed"=>extension_loaded('yaz'),
            "level"=>"info"
        );
        $tests["php.ext.zip"]=array(
            "title"=>"ZIP Archiver Extension",
            "passed"=>extension_loaded('zip'),
            "level"=>"info"
        );
        $tests["php.ext.zlib"]=array(
            "title"=>"ZLib Archiver Extension",
            "passed"=>extension_loaded('zlib'),
            "level"=>"info"
        );
    }
    
    protected function findMysqlVersion() {
        if(empty($this->MYSQL_VERSION)){
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
    
    protected function findErrors(){
        foreach($this->TESTS as $test){
            if(!$test["passed"]){
                if($test["level"]=="error") $this->ERROR_COUNT++;
                elseif($test["level"]=="warn") $this->WARNING_COUNT++;
            }
        }
    }
    
    public function isSuccess(){
        return $this->ERROR_COUNT==0;
    }
    
}

$servertest = new ServerTest();
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
        <?php echo <<< css
        <style>
            /*Twitter Bootstrap 2.0.3*/
            article,aside,details,figcaption,figure,footer,header,hgroup,nav,section{display:block}audio,canvas,video{display:inline-block;*display:inline;*zoom:1}
            audio:not([controls]){display:none}html{font-size:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}a:focus{outline:thin dotted #333;outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}a:hover,a:active{outline:0}sub,sup{position:relative;font-size:75%;line-height:0;vertical-align:baseline}sup{top:-0.5em}sub{bottom:-0.25em}img{max-width:100%;vertical-align:middle;border:0;-ms-interpolation-mode:bicubic}button,input,select,textarea{margin:0;font-size:100%;vertical-align:middle}button,input{*overflow:visible;line-height:normal}button::-moz-focus-inner,input::-moz-focus-inner{padding:0;border:0}button,input[type="button"],input[type="reset"],input[type="submit"]{cursor:pointer;-webkit-appearance:button}input[type="search"]{-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;-webkit-appearance:textfield}input[type="search"]::-webkit-search-decoration,input[type="search"]::-webkit-search-cancel-button{-webkit-appearance:none}textarea{overflow:auto;vertical-align:top}.clearfix{*zoom:1}.clearfix:before,.clearfix:after{display:table;content:""}.clearfix:after{clear:both}.hide-text{font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}
            .input-block-level{display:block;width:100%;min-height:28px;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box}body{margin:0;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:13px;line-height:18px;color:#333;background-color:#fff}a{color:#08c;text-decoration:none}a:hover{color:#005580;text-decoration:underline}.row{margin-left:-20px;*zoom:1}.row:before,.row:after{display:table;content:""}.row:after{clear:both}[class*="span"]{float:left;margin-left:20px}.container,.navbar-fixed-top .container,.navbar-fixed-bottom .container{width:940px}.span12{width:940px}.span11{width:860px}.span10{width:780px}.span9{width:700px}.span8{width:620px}.span7{width:540px}.span6{width:460px}.span5{width:380px}.span4{width:300px}.span3{width:220px}.span2{width:140px}.span1{width:60px}.offset12{margin-left:980px}.offset11{margin-left:900px}.offset10{margin-left:820px}.offset9{margin-left:740px}.offset8{margin-left:660px}.offset7{margin-left:580px}.offset6{margin-left:500px}.offset5{margin-left:420px}.offset4{margin-left:340px}.offset3{margin-left:260px}.offset2{margin-left:180px}.offset1{margin-left:100px}.row-fluid{width:100%;*zoom:1}.row-fluid:before,.row-fluid:after{display:table;content:""}.row-fluid:after{clear:both}
            .row-fluid [class*="span"]{display:block;float:left;width:100%;min-height:28px;margin-left:2.127659574%;*margin-left:2.0744680846382977%;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box}.row-fluid [class*="span"]:first-child{margin-left:0}.row-fluid .span12{width:99.99999998999999%;*width:99.94680850063828%}.row-fluid .span11{width:91.489361693%;*width:91.4361702036383%}.row-fluid .span10{width:82.97872339599999%;*width:82.92553190663828%}.row-fluid .span9{width:74.468085099%;*width:74.4148936096383%}.row-fluid .span8{width:65.95744680199999%;*width:65.90425531263828%}.row-fluid .span7{width:57.446808505%;*width:57.3936170156383%}.row-fluid .span6{width:48.93617020799999%;*width:48.88297871863829%}.row-fluid .span5{width:40.425531911%;*width:40.3723404216383%}.row-fluid .span4{width:31.914893614%;*width:31.8617021246383%}.row-fluid .span3{width:23.404255317%;*width:23.3510638276383%}.row-fluid .span2{width:14.89361702%;*width:14.8404255306383%}.row-fluid .span1{width:6.382978723%;*width:6.329787233638298%}.container{margin-right:auto;margin-left:auto;*zoom:1}.container:before,.container:after{display:table;content:""}.container:after{clear:both}.container-fluid{padding-right:20px;padding-left:20px;*zoom:1}.container-fluid:before,.container-fluid:after{display:table;content:""}.container-fluid:after{clear:both}p{margin:0 0 9px;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:13px;line-height:18px}p small{font-size:11px;color:#999}.lead{margin-bottom:18px;font-size:20px;font-weight:200;line-height:27px}
            h1,h2,h3,h4,h5,h6{margin:0;font-family:inherit;font-weight:bold;color:inherit;text-rendering:optimizelegibility}h1 small,h2 small,h3 small,h4 small,h5 small,h6 small{font-weight:normal;color:#999}h1{font-size:30px;line-height:36px}h1 small{font-size:18px}h2{font-size:24px;line-height:36px}h2 small{font-size:18px}h3{font-size:18px;line-height:27px}h3 small{font-size:14px}h4,h5,h6{line-height:18px}h4{font-size:14px}h4 small{font-size:12px}h5{font-size:12px}h6{font-size:11px;color:#999;text-transform:uppercase}.page-header{padding-bottom:17px;margin:18px 0;border-bottom:1px solid #eee}.page-header h1{line-height:1}ul,ol{padding:0;margin:0 0 9px 25px}ul ul,ul ol,ol ol,ol ul{margin-bottom:0}ul{list-style:disc}ol{list-style:decimal}li{line-height:18px}ul.unstyled,ol.unstyled{margin-left:0;list-style:none}dl{margin-bottom:18px}dt,dd{line-height:18px}dt{font-weight:bold;line-height:17px}dd{margin-left:9px}.dl-horizontal dt{float:left;width:120px;overflow:hidden;clear:left;text-align:right;text-overflow:ellipsis;white-space:nowrap}.dl-horizontal dd{margin-left:130px}hr{margin:18px 0;border:0;border-top:1px solid #eee;border-bottom:1px solid #fff}strong{font-weight:bold}em{font-style:italic}.muted{color:#999}abbr[title]{cursor:help;border-bottom:1px dotted #ddd}abbr.initialism{font-size:90%;text-transform:uppercase}blockquote{padding:0 0 0 15px;margin:0 0 18px;border-left:5px solid #eee}blockquote p{margin-bottom:0;font-size:16px;font-weight:300;line-height:22.5px}blockquote small{display:block;line-height:18px;color:#999}blockquote small:before{content:'\2014 \00A0'}
            blockquote.pull-right{float:right;padding-right:15px;padding-left:0;border-right:5px solid #eee;border-left:0}blockquote.pull-right p,blockquote.pull-right small{text-align:right}q:before,q:after,blockquote:before,blockquote:after{content:""}address{display:block;margin-bottom:18px;font-style:normal;line-height:18px}small{font-size:100%}cite{font-style:normal}code,pre{padding:0 3px 2px;font-family:Menlo,Monaco,Consolas,"Courier New",monospace;font-size:12px;color:#333;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px}code{padding:2px 4px;color:#d14;background-color:#f7f7f9;border:1px solid #e1e1e8}pre{display:block;padding:8.5px;margin:0 0 9px;font-size:12.025px;line-height:18px;word-break:break-all;word-wrap:break-word;white-space:pre;white-space:pre-wrap;background-color:#f5f5f5;border:1px solid #ccc;border:1px solid rgba(0,0,0,0.15);-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}pre.prettyprint{margin-bottom:18px}pre code{padding:0;color:inherit;background-color:transparent;border:0}.pre-scrollable{max-height:340px;overflow-y:scroll}form{margin:0 0 18px}fieldset{padding:0;margin:0;border:0}legend{display:block;width:100%;padding:0;margin-bottom:27px;font-size:19.5px;line-height:36px;color:#333;border:0;border-bottom:1px solid #eee}legend small{font-size:13.5px;color:#999}label,input,button,select,textarea{font-size:13px;font-weight:normal;line-height:18px}input,button,select,textarea{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif}label{display:block;margin-bottom:5px;color:#333}
            input,textarea,select,.uneditable-input{display:inline-block;width:210px;height:18px;padding:4px;margin-bottom:9px;font-size:13px;line-height:18px;color:#555;background-color:#fff;border:1px solid #ccc;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px}.uneditable-textarea{width:auto;height:auto}label input,label textarea,label select{display:block}input[type="image"],input[type="checkbox"],input[type="radio"]{width:auto;height:auto;padding:0;margin:3px 0;*margin-top:0;line-height:normal;cursor:pointer;background-color:transparent;border:0 \9;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}input[type="image"]{border:0}input[type="file"]{width:auto;padding:initial;line-height:initial;background-color:#fff;background-color:initial;border:initial;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}input[type="button"],input[type="reset"],input[type="submit"]{width:auto;height:auto}select,input[type="file"]{height:28px;*margin-top:4px;line-height:28px}input[type="file"]{line-height:18px \9}select{width:220px;background-color:#fff}select[multiple],select[size]{height:auto}input[type="image"]{-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}textarea{height:auto}input[type="hidden"]{display:none}.radio,.checkbox{min-height:18px;padding-left:18px}.radio input[type="radio"],.checkbox input[type="checkbox"]{float:left;margin-left:-18px}.controls>.radio:first-child,.controls>.checkbox:first-child{padding-top:5px}.radio.inline,.checkbox.inline{display:inline-block;padding-top:5px;margin-bottom:0;vertical-align:middle}
            .radio.inline+.radio.inline,.checkbox.inline+.checkbox.inline{margin-left:10px}input,textarea{-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075);-moz-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075);box-shadow:inset 0 1px 1px rgba(0,0,0,0.075);-webkit-transition:border linear .2s,box-shadow linear .2s;-moz-transition:border linear .2s,box-shadow linear .2s;-ms-transition:border linear .2s,box-shadow linear .2s;-o-transition:border linear .2s,box-shadow linear .2s;transition:border linear .2s,box-shadow linear .2s}input:focus,textarea:focus{border-color:rgba(82,168,236,0.8);outline:0;outline:thin dotted \9;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6);-moz-box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6);box-shadow:inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6)}input[type="file"]:focus,input[type="radio"]:focus,input[type="checkbox"]:focus,select:focus{outline:thin dotted #333;outline:5px auto -webkit-focus-ring-color;outline-offset:-2px;-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}.input-mini{width:60px}.input-small{width:90px}.input-medium{width:150px}.input-large{width:210px}.input-xlarge{width:270px}.input-xxlarge{width:530px}input[class*="span"],select[class*="span"],textarea[class*="span"],.uneditable-input[class*="span"],.row-fluid input[class*="span"],.row-fluid select[class*="span"],.row-fluid textarea[class*="span"],.row-fluid .uneditable-input[class*="span"]{float:none;margin-left:0}input,textarea,.uneditable-input{margin-left:0}input.span12,textarea.span12,.uneditable-input.span12{width:930px}
            input.span11,textarea.span11,.uneditable-input.span11{width:850px}input.span10,textarea.span10,.uneditable-input.span10{width:770px}input.span9,textarea.span9,.uneditable-input.span9{width:690px}input.span8,textarea.span8,.uneditable-input.span8{width:610px}input.span7,textarea.span7,.uneditable-input.span7{width:530px}input.span6,textarea.span6,.uneditable-input.span6{width:450px}input.span5,textarea.span5,.uneditable-input.span5{width:370px}input.span4,textarea.span4,.uneditable-input.span4{width:290px}input.span3,textarea.span3,.uneditable-input.span3{width:210px}input.span2,textarea.span2,.uneditable-input.span2{width:130px}input.span1,textarea.span1,.uneditable-input.span1{width:50px}input[disabled],select[disabled],textarea[disabled],input[readonly],select[readonly],textarea[readonly]{cursor:not-allowed;background-color:#eee;border-color:#ddd}input[type="radio"][disabled],input[type="checkbox"][disabled],input[type="radio"][readonly],input[type="checkbox"][readonly]{background-color:transparent}.control-group.warning>label,.control-group.warning .help-block,.control-group.warning .help-inline{color:#c09853}.control-group.warning input,.control-group.warning select,.control-group.warning textarea{color:#c09853;border-color:#c09853}.control-group.warning input:focus,.control-group.warning select:focus,.control-group.warning textarea:focus{border-color:#a47e3c;-webkit-box-shadow:0 0 6px #dbc59e;-moz-box-shadow:0 0 6px #dbc59e;box-shadow:0 0 6px #dbc59e}.control-group.warning .input-prepend .add-on,.control-group.warning .input-append .add-on{color:#c09853;background-color:#fcf8e3;border-color:#c09853}.control-group.error>label,.control-group.error .help-block,.control-group.error .help-inline{color:#b94a48}.control-group.error input,.control-group.error select,
            .control-group.error textarea{color:#b94a48;border-color:#b94a48}.control-group.error input:focus,.control-group.error select:focus,.control-group.error textarea:focus{border-color:#953b39;-webkit-box-shadow:0 0 6px #d59392;-moz-box-shadow:0 0 6px #d59392;box-shadow:0 0 6px #d59392}.control-group.error .input-prepend .add-on,.control-group.error .input-append .add-on{color:#b94a48;background-color:#f2dede;border-color:#b94a48}.control-group.success>label,.control-group.success .help-block,.control-group.success .help-inline{color:#468847}.control-group.success input,.control-group.success select,.control-group.success textarea{color:#468847;border-color:#468847}.control-group.success input:focus,.control-group.success select:focus,.control-group.success textarea:focus{border-color:#356635;-webkit-box-shadow:0 0 6px #7aba7b;-moz-box-shadow:0 0 6px #7aba7b;box-shadow:0 0 6px #7aba7b}.control-group.success .input-prepend .add-on,.control-group.success .input-append .add-on{color:#468847;background-color:#dff0d8;border-color:#468847}input:focus:required:invalid,textarea:focus:required:invalid,select:focus:required:invalid{color:#b94a48;border-color:#ee5f5b}input:focus:required:invalid:focus,textarea:focus:required:invalid:focus,select:focus:required:invalid:focus{border-color:#e9322d;-webkit-box-shadow:0 0 6px #f8b9b7;-moz-box-shadow:0 0 6px #f8b9b7;box-shadow:0 0 6px #f8b9b7}.form-actions{padding:17px 20px 18px;margin-top:18px;margin-bottom:18px;background-color:#f5f5f5;border-top:1px solid #ddd;*zoom:1}.form-actions:before,.form-actions:after{display:table;content:""}.form-actions:after{clear:both}
            .uneditable-input{overflow:hidden;white-space:nowrap;cursor:not-allowed;background-color:#fff;border-color:#eee;-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,0.025);-moz-box-shadow:inset 0 1px 2px rgba(0,0,0,0.025);box-shadow:inset 0 1px 2px rgba(0,0,0,0.025)}:-moz-placeholder{color:#999}::-webkit-input-placeholder{color:#999}.help-block,.help-inline{color:#555}.help-block{display:block;margin-bottom:9px}.help-inline{display:inline-block;*display:inline;padding-left:5px;vertical-align:middle;*zoom:1}.input-prepend,.input-append{margin-bottom:5px}.input-prepend input,.input-append input,.input-prepend select,.input-append select,.input-prepend .uneditable-input,.input-append .uneditable-input{position:relative;margin-bottom:0;*margin-left:0;vertical-align:middle;-webkit-border-radius:0 3px 3px 0;-moz-border-radius:0 3px 3px 0;border-radius:0 3px 3px 0}.input-prepend input:focus,.input-append input:focus,.input-prepend select:focus,.input-append select:focus,.input-prepend .uneditable-input:focus,.input-append .uneditable-input:focus{z-index:2}.input-prepend .uneditable-input,.input-append .uneditable-input{border-left-color:#ccc}.input-prepend .add-on,.input-append .add-on{display:inline-block;width:auto;height:18px;min-width:16px;padding:4px 5px;font-weight:normal;line-height:18px;text-align:center;text-shadow:0 1px 0 #fff;vertical-align:middle;background-color:#eee;border:1px solid #ccc}.input-prepend .add-on,.input-append .add-on,.input-prepend .btn,.input-append .btn{margin-left:-1px;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}.input-prepend .active,.input-append .active{background-color:#a9dba9;border-color:#46a546}.input-prepend .add-on,.input-prepend .btn{margin-right:-1px}.input-prepend .add-on:first-child,.input-prepend .btn:first-child{-webkit-border-radius:3px 0 0 3px;-moz-border-radius:3px 0 0 3px;border-radius:3px 0 0 3px}.input-append input,.input-append select,.input-append .uneditable-input{-webkit-border-radius:3px 0 0 3px;-moz-border-radius:3px 0 0 3px;border-radius:3px 0 0 3px}.input-append .uneditable-input{border-right-color:#ccc;border-left-color:#eee}.input-append .add-on:last-child,.input-append .btn:last-child{-webkit-border-radius:0 3px 3px 0;-moz-border-radius:0 3px 3px 0;border-radius:0 3px 3px 0}.input-prepend.input-append input,.input-prepend.input-append select,.input-prepend.input-append .uneditable-input{-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}.input-prepend.input-append .add-on:first-child,.input-prepend.input-append .btn:first-child{margin-right:-1px;-webkit-border-radius:3px 0 0 3px;-moz-border-radius:3px 0 0 3px;border-radius:3px 0 0 3px}.input-prepend.input-append .add-on:last-child,.input-prepend.input-append .btn:last-child{margin-left:-1px;-webkit-border-radius:0 3px 3px 0;-moz-border-radius:0 3px 3px 0;border-radius:0 3px 3px 0}.search-query{padding-right:14px;padding-right:4px \9;padding-left:14px;padding-left:4px \9;margin-bottom:0;-webkit-border-radius:14px;-moz-border-radius:14px;border-radius:14px}.form-search input,.form-inline input,.form-horizontal input,.form-search textarea,.form-inline textarea,.form-horizontal textarea,.form-search select,.form-inline select,.form-horizontal select,.form-search .help-inline,.form-inline .help-inline,.form-horizontal .help-inline,.form-search .uneditable-input,.form-inline .uneditable-input,.form-horizontal .uneditable-input,.form-search .input-prepend,.form-inline .input-prepend,.form-horizontal .input-prepend,.form-search .input-append,.form-inline .input-append,.form-horizontal .input-append{display:inline-block;*display:inline;margin-bottom:0;*zoom:1}.form-search .hide,.form-inline .hide,.form-horizontal .hide{display:none}.form-search label,.form-inline label{display:inline-block}.form-search .input-append,.form-inline .input-append,.form-search .input-prepend,.form-inline .input-prepend{margin-bottom:0}.form-search .radio,.form-search .checkbox,.form-inline .radio,.form-inline .checkbox{padding-left:0;margin-bottom:0;vertical-align:middle}.form-search .radio input[type="radio"],.form-search .checkbox input[type="checkbox"],.form-inline .radio input[type="radio"],.form-inline .checkbox input[type="checkbox"]{float:left;margin-right:3px;margin-left:0}.control-group{margin-bottom:9px}legend+.control-group{margin-top:18px;-webkit-margin-top-collapse:separate}.form-horizontal .control-group{margin-bottom:18px;*zoom:1}.form-horizontal .control-group:before,.form-horizontal .control-group:after{display:table;content:""}.form-horizontal .control-group:after{clear:both}.form-horizontal .control-label{float:left;width:140px;padding-top:5px;text-align:right}.form-horizontal .controls{*display:inline-block;*padding-left:20px;margin-left:160px;*margin-left:0}.form-horizontal .controls:first-child{*padding-left:160px}.form-horizontal .help-block{margin-top:9px;margin-bottom:0}.form-horizontal .form-actions{padding-left:160px}table{max-width:100%;background-color:transparent;border-collapse:collapse;border-spacing:0}.table{width:100%;margin-bottom:18px}.table th,.table td{padding:8px;line-height:18px;text-align:left;vertical-align:top;border-top:1px solid #ddd}.table th{font-weight:bold}.table thead th{vertical-align:bottom}.table caption+thead tr:first-child th,.table caption+thead tr:first-child td,.table colgroup+thead tr:first-child th,.table colgroup+thead tr:first-child td,.table thead:first-child tr:first-child th,.table thead:first-child tr:first-child td{border-top:0}.table tbody+tbody{border-top:2px solid #ddd}.table-condensed th,.table-condensed td{padding:4px 5px}.table-bordered{border:1px solid #ddd;border-collapse:separate;*border-collapse:collapsed;border-left:0;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.table-bordered th,.table-bordered td{border-left:1px solid #ddd}.table-bordered caption+thead tr:first-child th,.table-bordered caption+tbody tr:first-child th,.table-bordered caption+tbody tr:first-child td,.table-bordered colgroup+thead tr:first-child th,.table-bordered colgroup+tbody tr:first-child th,.table-bordered colgroup+tbody tr:first-child td,.table-bordered thead:first-child tr:first-child th,.table-bordered tbody:first-child tr:first-child th,.table-bordered tbody:first-child tr:first-child td{border-top:0}.table-bordered thead:first-child tr:first-child th:first-child,
            .table-bordered tbody:first-child tr:first-child td:first-child{-webkit-border-top-left-radius:4px;border-top-left-radius:4px;-moz-border-radius-topleft:4px}.table-bordered thead:first-child tr:first-child th:last-child,.table-bordered tbody:first-child tr:first-child td:last-child{-webkit-border-top-right-radius:4px;border-top-right-radius:4px;-moz-border-radius-topright:4px}.table-bordered thead:last-child tr:last-child th:first-child,.table-bordered tbody:last-child tr:last-child td:first-child{-webkit-border-radius:0 0 0 4px;-moz-border-radius:0 0 0 4px;border-radius:0 0 0 4px;-webkit-border-bottom-left-radius:4px;border-bottom-left-radius:4px;-moz-border-radius-bottomleft:4px}.table-bordered thead:last-child tr:last-child th:last-child,.table-bordered tbody:last-child tr:last-child td:last-child{-webkit-border-bottom-right-radius:4px;border-bottom-right-radius:4px;-moz-border-radius-bottomright:4px}.table-striped tbody tr:nth-child(odd) td,.table-striped tbody tr:nth-child(odd) th{background-color:#f9f9f9}.table tbody tr:hover td,.table tbody tr:hover th{background-color:#f5f5f5}table .span1{float:none;width:44px;margin-left:0}table .span2{float:none;width:124px;margin-left:0}table .span3{float:none;width:204px;margin-left:0}table .span4{float:none;width:284px;margin-left:0}table .span5{float:none;width:364px;margin-left:0}table .span6{float:none;width:444px;margin-left:0}table .span7{float:none;width:524px;margin-left:0}table .span8{float:none;width:604px;margin-left:0}table .span9{float:none;width:684px;margin-left:0}table .span10{float:none;width:764px;margin-left:0}table .span11{float:none;width:844px;margin-left:0}table .span12{float:none;width:924px;margin-left:0}table .span13{float:none;width:1004px;margin-left:0}table .span14{float:none;width:1084px;margin-left:0}table .span15{float:none;width:1164px;margin-left:0}table .span16{float:none;width:1244px;margin-left:0}table .span17{float:none;width:1324px;margin-left:0}table .span18{float:none;width:1404px;margin-left:0}table .span19{float:none;width:1484px;margin-left:0}table .span20{float:none;width:1564px;margin-left:0}table .span21{float:none;width:1644px;margin-left:0}table .span22{float:none;width:1724px;margin-left:0}table .span23{float:none;width:1804px;margin-left:0}table .span24{float:none;width:1884px;margin-left:0}[class^="icon-"],[class*=" icon-"]{display:inline-block;width:14px;height:14px;*margin-right:.3em;line-height:14px;vertical-align:text-top;background-image:url("../img/glyphicons-halflings.png");background-position:14px 14px;background-repeat:no-repeat}[class^="icon-"]:last-child,[class*=" icon-"]:last-child{*margin-left:0}.icon-white{background-image:url("../img/glyphicons-halflings-white.png")}.icon-glass{background-position:0 0}.icon-music{background-position:-24px 0}.icon-search{background-position:-48px 0}.icon-envelope{background-position:-72px 0}.icon-heart{background-position:-96px 0}.icon-star{background-position:-120px 0}.icon-star-empty{background-position:-144px 0}.icon-user{background-position:-168px 0}.icon-film{background-position:-192px 0}.icon-th-large{background-position:-216px 0}.icon-th{background-position:-240px 0}.icon-th-list{background-position:-264px 0}.icon-ok{background-position:-288px 0}.icon-remove{background-position:-312px 0}.icon-zoom-in{background-position:-336px 0}.icon-zoom-out{background-position:-360px 0}.icon-off{background-position:-384px 0}.icon-signal{background-position:-408px 0}.icon-cog{background-position:-432px 0}.icon-trash{background-position:-456px 0}.icon-home{background-position:0 -24px}.icon-file{background-position:-24px -24px}.icon-time{background-position:-48px -24px}.icon-road{background-position:-72px -24px}.icon-download-alt{background-position:-96px -24px}.icon-download{background-position:-120px -24px}.icon-upload{background-position:-144px -24px}.icon-inbox{background-position:-168px -24px}.icon-play-circle{background-position:-192px -24px}.icon-repeat{background-position:-216px -24px}.icon-refresh{background-position:-240px -24px}.icon-list-alt{background-position:-264px -24px}.icon-lock{background-position:-287px -24px}.icon-flag{background-position:-312px -24px}.icon-headphones{background-position:-336px -24px}.icon-volume-off{background-position:-360px -24px}.icon-volume-down{background-position:-384px -24px}.icon-volume-up{background-position:-408px -24px}.icon-qrcode{background-position:-432px -24px}.icon-barcode{background-position:-456px -24px}.icon-tag{background-position:0 -48px}.icon-tags{background-position:-25px -48px}.icon-book{background-position:-48px -48px}.icon-bookmark{background-position:-72px -48px}.icon-print{background-position:-96px -48px}.icon-camera{background-position:-120px -48px}.icon-font{background-position:-144px -48px}.icon-bold{background-position:-167px -48px}.icon-italic{background-position:-192px -48px}.icon-text-height{background-position:-216px -48px}.icon-text-width{background-position:-240px -48px}.icon-align-left{background-position:-264px -48px}.icon-align-center{background-position:-288px -48px}.icon-align-right{background-position:-312px -48px}.icon-align-justify{background-position:-336px -48px}.icon-list{background-position:-360px -48px}.icon-indent-left{background-position:-384px -48px}.icon-indent-right{background-position:-408px -48px}.icon-facetime-video{background-position:-432px -48px}.icon-picture{background-position:-456px -48px}.icon-pencil{background-position:0 -72px}.icon-map-marker{background-position:-24px -72px}.icon-adjust{background-position:-48px -72px}.icon-tint{background-position:-72px -72px}.icon-edit{background-position:-96px -72px}.icon-share{background-position:-120px -72px}.icon-check{background-position:-144px -72px}.icon-move{background-position:-168px -72px}.icon-step-backward{background-position:-192px -72px}.icon-fast-backward{background-position:-216px -72px}.icon-backward{background-position:-240px -72px}.icon-play{background-position:-264px -72px}.icon-pause{background-position:-288px -72px}.icon-stop{background-position:-312px -72px}.icon-forward{background-position:-336px -72px}.icon-fast-forward{background-position:-360px -72px}.icon-step-forward{background-position:-384px -72px}.icon-eject{background-position:-408px -72px}.icon-chevron-left{background-position:-432px -72px}
            .icon-chevron-right{background-position:-456px -72px}.icon-plus-sign{background-position:0 -96px}.icon-minus-sign{background-position:-24px -96px}.icon-remove-sign{background-position:-48px -96px}.icon-ok-sign{background-position:-72px -96px}.icon-question-sign{background-position:-96px -96px}.icon-info-sign{background-position:-120px -96px}.icon-screenshot{background-position:-144px -96px}.icon-remove-circle{background-position:-168px -96px}.icon-ok-circle{background-position:-192px -96px}.icon-ban-circle{background-position:-216px -96px}.icon-arrow-left{background-position:-240px -96px}.icon-arrow-right{background-position:-264px -96px}.icon-arrow-up{background-position:-289px -96px}.icon-arrow-down{background-position:-312px -96px}.icon-share-alt{background-position:-336px -96px}.icon-resize-full{background-position:-360px -96px}.icon-resize-small{background-position:-384px -96px}.icon-plus{background-position:-408px -96px}.icon-minus{background-position:-433px -96px}.icon-asterisk{background-position:-456px -96px}.icon-exclamation-sign{background-position:0 -120px}.icon-gift{background-position:-24px -120px}.icon-leaf{background-position:-48px -120px}.icon-fire{background-position:-72px -120px}.icon-eye-open{background-position:-96px -120px}.icon-eye-close{background-position:-120px -120px}.icon-warning-sign{background-position:-144px -120px}.icon-plane{background-position:-168px -120px}.icon-calendar{background-position:-192px -120px}.icon-random{background-position:-216px -120px}.icon-comment{background-position:-240px -120px}.icon-magnet{background-position:-264px -120px}.icon-chevron-up{background-position:-288px -120px}.icon-chevron-down{background-position:-313px -119px}.icon-retweet{background-position:-336px -120px}.icon-shopping-cart{background-position:-360px -120px}.icon-folder-close{background-position:-384px -120px}.icon-folder-open{background-position:-408px -120px}.icon-resize-vertical{background-position:-432px -119px}.icon-resize-horizontal{background-position:-456px -118px}.icon-hdd{background-position:0 -144px}.icon-bullhorn{background-position:-24px -144px}.icon-bell{background-position:-48px -144px}.icon-certificate{background-position:-72px -144px}.icon-thumbs-up{background-position:-96px -144px}.icon-thumbs-down{background-position:-120px -144px}.icon-hand-right{background-position:-144px -144px}.icon-hand-left{background-position:-168px -144px}.icon-hand-up{background-position:-192px -144px}.icon-hand-down{background-position:-216px -144px}.icon-circle-arrow-right{background-position:-240px -144px}.icon-circle-arrow-left{background-position:-264px -144px}.icon-circle-arrow-up{background-position:-288px -144px}.icon-circle-arrow-down{background-position:-312px -144px}.icon-globe{background-position:-336px -144px}.icon-wrench{background-position:-360px -144px}.icon-tasks{background-position:-384px -144px}.icon-filter{background-position:-408px -144px}.icon-briefcase{background-position:-432px -144px}.icon-fullscreen{background-position:-456px -144px}.dropup,.dropdown{position:relative}.dropdown-toggle{*margin-bottom:-3px}.dropdown-toggle:active,.open .dropdown-toggle{outline:0}.caret{display:inline-block;width:0;height:0;vertical-align:top;border-top:4px solid #000;border-right:4px solid transparent;border-left:4px solid transparent;content:"";opacity:.3;filter:alpha(opacity=30)}.dropdown .caret{margin-top:8px;margin-left:2px}.dropdown:hover .caret,.open .caret{opacity:1;filter:alpha(opacity=100)}.dropdown-menu{position:absolute;top:100%;left:0;z-index:1000;display:none;float:left;min-width:160px;padding:4px 0;margin:1px 0 0;list-style:none;background-color:#fff;border:1px solid #ccc;border:1px solid rgba(0,0,0,0.2);*border-right-width:2px;*border-bottom-width:2px;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;-webkit-box-shadow:0 5px 10px rgba(0,0,0,0.2);-moz-box-shadow:0 5px 10px rgba(0,0,0,0.2);box-shadow:0 5px 10px rgba(0,0,0,0.2);-webkit-background-clip:padding-box;-moz-background-clip:padding;background-clip:padding-box}.dropdown-menu.pull-right{right:0;left:auto}.dropdown-menu .divider{*width:100%;height:1px;margin:8px 1px;*margin:-5px 0 5px;overflow:hidden;background-color:#e5e5e5;border-bottom:1px solid #fff}.dropdown-menu a{display:block;padding:3px 15px;clear:both;font-weight:normal;line-height:18px;color:#333;white-space:nowrap}.dropdown-menu li>a:hover,.dropdown-menu .active>a,.dropdown-menu .active>a:hover{color:#fff;text-decoration:none;background-color:#08c}.open{*z-index:1000}.open .dropdown-menu{display:block}.pull-right .dropdown-menu{right:0;left:auto}.dropup .caret,.navbar-fixed-bottom .dropdown .caret{border-top:0;border-bottom:4px solid #000;content:"\2191"}.dropup .dropdown-menu,.navbar-fixed-bottom .dropdown .dropdown-menu{top:auto;bottom:100%;margin-bottom:1px}.typeahead{margin-top:2px;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.well{min-height:20px;padding:19px;margin-bottom:20px;background-color:#f5f5f5;border:1px solid #eee;border:1px solid rgba(0,0,0,0.05);-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,0.05);-moz-box-shadow:inset 0 1px 1px rgba(0,0,0,0.05);box-shadow:inset 0 1px 1px rgba(0,0,0,0.05)}.well blockquote{border-color:#ddd;border-color:rgba(0,0,0,0.15)}.well-large{padding:24px;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px}.well-small{padding:9px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px}.fade{opacity:0;filter:alpha(opacity=0);-webkit-transition:opacity .15s linear;-moz-transition:opacity .15s linear;-ms-transition:opacity .15s linear;-o-transition:opacity .15s linear;transition:opacity .15s linear}.fade.in{opacity:1;filter:alpha(opacity=100)}.collapse{position:relative;height:0;overflow:hidden;-webkit-transition:height .35s ease;-moz-transition:height .35s ease;-ms-transition:height .35s ease;-o-transition:height .35s ease;transition:height .35s ease}.collapse.in{height:auto}.close{float:right;font-size:20px;font-weight:bold;line-height:18px;color:#000;text-shadow:0 1px 0 #fff;opacity:.2;filter:alpha(opacity=20)}.close:hover{color:#000;text-decoration:none;cursor:pointer;opacity:.4;filter:alpha(opacity=40)}button.close{padding:0;cursor:pointer;background:transparent;border:0;-webkit-appearance:none}.btn{display:inline-block;*display:inline;padding:4px 10px 4px;margin-bottom:0;*margin-left:.3em;font-size:13px;line-height:18px;*line-height:20px;color:#333;text-align:center;text-shadow:0 1px 1px rgba(255,255,255,0.75);vertical-align:middle;cursor:pointer;background-color:#f5f5f5;*background-color:#e6e6e6;background-image:-ms-linear-gradient(top,#fff,#e6e6e6);background-image:-webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6));background-image:-webkit-linear-gradient(top,#fff,#e6e6e6);background-image:-o-linear-gradient(top,#fff,#e6e6e6);background-image:linear-gradient(top,#fff,#e6e6e6);background-image:-moz-linear-gradient(top,#fff,#e6e6e6);background-repeat:repeat-x;border:1px solid #ccc;*border:0;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);border-color:#e6e6e6 #e6e6e6 #bfbfbf;border-bottom-color:#b3b3b3;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#ffffff',endColorstr='#e6e6e6',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false);*zoom:1;-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow:inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow:inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05)}.btn:hover,.btn:active,.btn.active,.btn.disabled,.btn[disabled]{background-color:#e6e6e6;*background-color:#d9d9d9}.btn:active,.btn.active{background-color:#ccc \9}.btn:first-child{*margin-left:0}.btn:hover{color:#333;text-decoration:none;background-color:#e6e6e6;*background-color:#d9d9d9;background-position:0 -15px;-webkit-transition:background-position .1s linear;-moz-transition:background-position .1s linear;-ms-transition:background-position .1s linear;-o-transition:background-position .1s linear;transition:background-position .1s linear}.btn:focus{outline:thin dotted #333;outline:5px auto -webkit-focus-ring-color;outline-offset:-2px}.btn.active,.btn:active{background-color:#e6e6e6;background-color:#d9d9d9 \9;background-image:none;outline:0;-webkit-box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05)}.btn.disabled,.btn[disabled]{cursor:default;background-color:#e6e6e6;background-image:none;opacity:.65;filter:alpha(opacity=65);-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none}.btn-large{padding:9px 14px;font-size:15px;line-height:normal;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px}.btn-large [class^="icon-"]{margin-top:1px}.btn-small{padding:5px 9px;font-size:11px;line-height:16px}.btn-small [class^="icon-"]{margin-top:-1px}.btn-mini{padding:2px 6px;font-size:11px;line-height:14px}.btn-primary,.btn-primary:hover,.btn-warning,.btn-warning:hover,.btn-danger,.btn-danger:hover,.btn-success,.btn-success:hover,.btn-info,.btn-info:hover,.btn-inverse,.btn-inverse:hover{color:#fff;text-shadow:0 -1px 0 rgba(0,0,0,0.25)}.btn-primary.active,.btn-warning.active,.btn-danger.active,.btn-success.active,.btn-info.active,.btn-inverse.active{color:rgba(255,255,255,0.75)}.btn{border-color:#ccc;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25)}.btn-primary{background-color:#0074cc;*background-color:#05c;background-image:-ms-linear-gradient(top,#08c,#05c);background-image:-webkit-gradient(linear,0 0,0 100%,from(#08c),to(#05c));background-image:-webkit-linear-gradient(top,#08c,#05c);background-image:-o-linear-gradient(top,#08c,#05c);background-image:-moz-linear-gradient(top,#08c,#05c);background-image:linear-gradient(top,#08c,#05c);background-repeat:repeat-x;border-color:#05c #05c #003580;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#0088cc',endColorstr='#0055cc',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-primary:hover,.btn-primary:active,.btn-primary.active,.btn-primary.disabled,.btn-primary[disabled]{background-color:#05c;*background-color:#004ab3}.btn-primary:active,.btn-primary.active{background-color:#004099 \9}.btn-warning{background-color:#faa732;*background-color:#f89406;background-image:-ms-linear-gradient(top,#fbb450,#f89406);background-image:-webkit-gradient(linear,0 0,0 100%,from(#fbb450),to(#f89406));background-image:-webkit-linear-gradient(top,#fbb450,#f89406);background-image:-o-linear-gradient(top,#fbb450,#f89406);background-image:-moz-linear-gradient(top,#fbb450,#f89406);background-image:linear-gradient(top,#fbb450,#f89406);background-repeat:repeat-x;border-color:#f89406 #f89406 #ad6704;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#fbb450',endColorstr='#f89406',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-warning:hover,.btn-warning:active,.btn-warning.active,.btn-warning.disabled,.btn-warning[disabled]{background-color:#f89406;*background-color:#df8505}.btn-warning:active,.btn-warning.active{background-color:#c67605 \9}.btn-danger{background-color:#da4f49;*background-color:#bd362f;background-image:-ms-linear-gradient(top,#ee5f5b,#bd362f);background-image:-webkit-gradient(linear,0 0,0 100%,from(#ee5f5b),to(#bd362f));background-image:-webkit-linear-gradient(top,#ee5f5b,#bd362f);background-image:-o-linear-gradient(top,#ee5f5b,#bd362f);background-image:-moz-linear-gradient(top,#ee5f5b,#bd362f);background-image:linear-gradient(top,#ee5f5b,#bd362f);background-repeat:repeat-x;border-color:#bd362f #bd362f #802420;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#ee5f5b',endColorstr='#bd362f',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-danger:hover,.btn-danger:active,.btn-danger.active,.btn-danger.disabled,.btn-danger[disabled]{background-color:#bd362f;*background-color:#a9302a}.btn-danger:active,.btn-danger.active{background-color:#942a25 \9}.btn-success{background-color:#5bb75b;*background-color:#51a351;background-image:-ms-linear-gradient(top,#62c462,#51a351);background-image:-webkit-gradient(linear,0 0,0 100%,from(#62c462),to(#51a351));background-image:-webkit-linear-gradient(top,#62c462,#51a351);background-image:-o-linear-gradient(top,#62c462,#51a351);background-image:-moz-linear-gradient(top,#62c462,#51a351);background-image:linear-gradient(top,#62c462,#51a351);background-repeat:repeat-x;border-color:#51a351 #51a351 #387038;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#62c462',endColorstr='#51a351',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-success:hover,.btn-success:active,.btn-success.active,.btn-success.disabled,.btn-success[disabled]{background-color:#51a351;*background-color:#499249}.btn-success:active,.btn-success.active{background-color:#408140 \9}.btn-info{background-color:#49afcd;*background-color:#2f96b4;background-image:-ms-linear-gradient(top,#5bc0de,#2f96b4);background-image:-webkit-gradient(linear,0 0,0 100%,from(#5bc0de),to(#2f96b4));background-image:-webkit-linear-gradient(top,#5bc0de,#2f96b4);background-image:-o-linear-gradient(top,#5bc0de,#2f96b4);background-image:-moz-linear-gradient(top,#5bc0de,#2f96b4);background-image:linear-gradient(top,#5bc0de,#2f96b4);background-repeat:repeat-x;border-color:#2f96b4 #2f96b4 #1f6377;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#5bc0de',endColorstr='#2f96b4',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-info:hover,.btn-info:active,.btn-info.active,.btn-info.disabled,.btn-info[disabled]{background-color:#2f96b4;*background-color:#2a85a0}.btn-info:active,.btn-info.active{background-color:#24748c \9}.btn-inverse{background-color:#414141;*background-color:#222;background-image:-ms-linear-gradient(top,#555,#222);background-image:-webkit-gradient(linear,0 0,0 100%,from(#555),to(#222));background-image:-webkit-linear-gradient(top,#555,#222);background-image:-o-linear-gradient(top,#555,#222);background-image:-moz-linear-gradient(top,#555,#222);background-image:linear-gradient(top,#555,#222);background-repeat:repeat-x;border-color:#222 #222 #000;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#555555',endColorstr='#222222',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false)}.btn-inverse:hover,.btn-inverse:active,.btn-inverse.active,.btn-inverse.disabled,.btn-inverse[disabled]{background-color:#222;*background-color:#151515}.btn-inverse:active,.btn-inverse.active{background-color:#080808 \9}button.btn,input[type="submit"].btn{*padding-top:2px;*padding-bottom:2px}button.btn::-moz-focus-inner,input[type="submit"].btn::-moz-focus-inner{padding:0;border:0}button.btn.btn-large,input[type="submit"].btn.btn-large{*padding-top:7px;*padding-bottom:7px}button.btn.btn-small,input[type="submit"].btn.btn-small{*padding-top:3px;*padding-bottom:3px}button.btn.btn-mini,input[type="submit"].btn.btn-mini{*padding-top:1px;*padding-bottom:1px}.btn-group{position:relative;*margin-left:.3em;*zoom:1}.btn-group:before,.btn-group:after{display:table;content:""}.btn-group:after{clear:both}.btn-group:first-child{*margin-left:0}.btn-group+.btn-group{margin-left:5px}.btn-toolbar{margin-top:9px;margin-bottom:9px}.btn-toolbar .btn-group{display:inline-block;*display:inline;*zoom:1}.btn-group>.btn{position:relative;float:left;margin-left:-1px;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}.btn-group>.btn:first-child{margin-left:0;-webkit-border-bottom-left-radius:4px;border-bottom-left-radius:4px;-webkit-border-top-left-radius:4px;border-top-left-radius:4px;-moz-border-radius-bottomleft:4px;-moz-border-radius-topleft:4px}.btn-group>.btn:last-child,.btn-group>.dropdown-toggle{-webkit-border-top-right-radius:4px;border-top-right-radius:4px;-webkit-border-bottom-right-radius:4px;border-bottom-right-radius:4px;-moz-border-radius-topright:4px;-moz-border-radius-bottomright:4px}.btn-group>.btn.large:first-child{margin-left:0;-webkit-border-bottom-left-radius:6px;border-bottom-left-radius:6px;-webkit-border-top-left-radius:6px;border-top-left-radius:6px;-moz-border-radius-bottomleft:6px;-moz-border-radius-topleft:6px}.btn-group>.btn.large:last-child,.btn-group>.large.dropdown-toggle{-webkit-border-top-right-radius:6px;border-top-right-radius:6px;-webkit-border-bottom-right-radius:6px;border-bottom-right-radius:6px;-moz-border-radius-topright:6px;-moz-border-radius-bottomright:6px}.btn-group>.btn:hover,.btn-group>.btn:focus,.btn-group>.btn:active,.btn-group>.btn.active{z-index:2}.btn-group .dropdown-toggle:active,.btn-group.open .dropdown-toggle{outline:0}.btn-group>.dropdown-toggle{*padding-top:4px;padding-right:8px;*padding-bottom:4px;padding-left:8px;-webkit-box-shadow:inset 1px 0 0 rgba(255,255,255,0.125),inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow:inset 1px 0 0 rgba(255,255,255,0.125),inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);box-shadow:inset 1px 0 0 rgba(255,255,255,0.125),inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05)}.btn-group>.btn-mini.dropdown-toggle{padding-right:5px;padding-left:5px}.btn-group>.btn-small.dropdown-toggle{*padding-top:4px;*padding-bottom:4px}.btn-group>.btn-large.dropdown-toggle{padding-right:12px;padding-left:12px}.btn-group.open .dropdown-toggle{background-image:none;-webkit-box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);box-shadow:inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05)}.btn-group.open .btn.dropdown-toggle{background-color:#e6e6e6}.btn-group.open .btn-primary.dropdown-toggle{background-color:#05c}.btn-group.open .btn-warning.dropdown-toggle{background-color:#f89406}.btn-group.open .btn-danger.dropdown-toggle{background-color:#bd362f}.btn-group.open .btn-success.dropdown-toggle{background-color:#51a351}.btn-group.open .btn-info.dropdown-toggle{background-color:#2f96b4}.btn-group.open .btn-inverse.dropdown-toggle{background-color:#222}.btn .caret{margin-top:7px;margin-left:0}.btn:hover .caret,.open.btn-group .caret{opacity:1;filter:alpha(opacity=100)}.btn-mini .caret{margin-top:5px}.btn-small .caret{margin-top:6px}.btn-large .caret{margin-top:6px;border-top-width:5px;border-right-width:5px;border-left-width:5px}.dropup .btn-large .caret{border-top:0;border-bottom:5px solid #000}.btn-primary .caret,.btn-warning .caret,.btn-danger .caret,.btn-info .caret,.btn-success .caret,.btn-inverse .caret{border-top-color:#fff;border-bottom-color:#fff;opacity:.75;filter:alpha(opacity=75)}.alert{padding:8px 35px 8px 14px;margin-bottom:18px;color:#c09853;text-shadow:0 1px 0 rgba(255,255,255,0.5);background-color:#fcf8e3;border:1px solid #fbeed5;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.alert-heading{color:inherit}.alert .close{position:relative;top:-2px;right:-21px;line-height:18px}.alert-success{color:#468847;background-color:#dff0d8;border-color:#d6e9c6}.alert-danger,.alert-error{color:#b94a48;background-color:#f2dede;border-color:#eed3d7}.alert-info{color:#3a87ad;background-color:#d9edf7;border-color:#bce8f1}.alert-block{padding-top:14px;padding-bottom:14px}.alert-block>p,.alert-block>ul{margin-bottom:0}.alert-block p+p{margin-top:5px}.nav{margin-bottom:18px;margin-left:0;list-style:none}.nav>li>a{display:block}.nav>li>a:hover{text-decoration:none;background-color:#eee}.nav>.pull-right{float:right}.nav .nav-header{display:block;padding:3px 15px;font-size:11px;font-weight:bold;line-height:18px;color:#999;text-shadow:0 1px 0 rgba(255,255,255,0.5);text-transform:uppercase}.nav li+.nav-header{margin-top:9px}.nav-list{padding-right:15px;padding-left:15px;margin-bottom:0}.nav-list>li>a,.nav-list .nav-header{margin-right:-15px;margin-left:-15px;text-shadow:0 1px 0 rgba(255,255,255,0.5)}.nav-list>li>a{padding:3px 15px}.nav-list>.active>a,.nav-list>.active>a:hover{color:#fff;text-shadow:0 -1px 0 rgba(0,0,0,0.2);background-color:#08c}.nav-list [class^="icon-"]{margin-right:2px}.nav-list .divider{*width:100%;height:1px;margin:8px 1px;*margin:-5px 0 5px;overflow:hidden;background-color:#e5e5e5;border-bottom:1px solid #fff}.nav-tabs,.nav-pills{*zoom:1}.nav-tabs:before,.nav-pills:before,.nav-tabs:after,.nav-pills:after{display:table;content:""}.nav-tabs:after,.nav-pills:after{clear:both}.nav-tabs>li,.nav-pills>li{float:left}.nav-tabs>li>a,.nav-pills>li>a{padding-right:12px;padding-left:12px;margin-right:2px;line-height:14px}.nav-tabs{border-bottom:1px solid #ddd}.nav-tabs>li{margin-bottom:-1px}.nav-tabs>li>a{padding-top:8px;padding-bottom:8px;line-height:18px;border:1px solid transparent;-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.nav-tabs>li>a:hover{border-color:#eee #eee #ddd}.nav-tabs>.active>a,.nav-tabs>.active>a:hover{color:#555;cursor:default;background-color:#fff;border:1px solid #ddd;border-bottom-color:transparent}.nav-pills>li>a{padding-top:8px;padding-bottom:8px;margin-top:2px;margin-bottom:2px;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px}.nav-pills>.active>a,.nav-pills>.active>a:hover{color:#fff;background-color:#08c}.nav-stacked>li{float:none}.nav-stacked>li>a{margin-right:0}.nav-tabs.nav-stacked{border-bottom:0}.nav-tabs.nav-stacked>li>a{border:1px solid #ddd;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}.nav-tabs.nav-stacked>li:first-child>a{-webkit-border-radius:4px 4px 0 0;-moz-border-radius:4px 4px 0 0;border-radius:4px 4px 0 0}.nav-tabs.nav-stacked>li:last-child>a{-webkit-border-radius:0 0 4px 4px;-moz-border-radius:0 0 4px 4px;border-radius:0 0 4px 4px}.nav-tabs.nav-stacked>li>a:hover{z-index:2;border-color:#ddd}.nav-pills.nav-stacked>li>a{margin-bottom:3px}.nav-pills.nav-stacked>li:last-child>a{margin-bottom:1px}.nav-tabs .dropdown-menu{-webkit-border-radius:0 0 5px 5px;-moz-border-radius:0 0 5px 5px;border-radius:0 0 5px 5px}.nav-pills .dropdown-menu{-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.nav-tabs .dropdown-toggle .caret,.nav-pills .dropdown-toggle .caret{margin-top:6px;border-top-color:#08c;border-bottom-color:#08c}.nav-tabs .dropdown-toggle:hover .caret,.nav-pills .dropdown-toggle:hover .caret{border-top-color:#005580;border-bottom-color:#005580}.nav-tabs .active .dropdown-toggle .caret,.nav-pills .active .dropdown-toggle .caret{border-top-color:#333;border-bottom-color:#333}.nav>.dropdown.active>a:hover{color:#000;cursor:pointer}.nav-tabs .open .dropdown-toggle,.nav-pills .open .dropdown-toggle,.nav>li.dropdown.open.active>a:hover{color:#fff;background-color:#999;border-color:#999}.nav li.dropdown.open .caret,.nav li.dropdown.open.active .caret,
            .nav li.dropdown.open a:hover .caret{border-top-color:#fff;border-bottom-color:#fff;opacity:1;filter:alpha(opacity=100)}.tabs-stacked .open>a:hover{border-color:#999}.tabbable{*zoom:1}.tabbable:before,.tabbable:after{display:table;content:""}.tabbable:after{clear:both}.tab-content{overflow:auto}.tabs-below>.nav-tabs,.tabs-right>.nav-tabs,.tabs-left>.nav-tabs{border-bottom:0}.tab-content>.tab-pane,.pill-content>.pill-pane{display:none}.tab-content>.active,.pill-content>.active{display:block}.tabs-below>.nav-tabs{border-top:1px solid #ddd}.tabs-below>.nav-tabs>li{margin-top:-1px;margin-bottom:0}.tabs-below>.nav-tabs>li>a{-webkit-border-radius:0 0 4px 4px;-moz-border-radius:0 0 4px 4px;border-radius:0 0 4px 4px}.tabs-below>.nav-tabs>li>a:hover{border-top-color:#ddd;border-bottom-color:transparent}.tabs-below>.nav-tabs>.active>a,.tabs-below>.nav-tabs>.active>a:hover{border-color:transparent #ddd #ddd #ddd}.tabs-left>.nav-tabs>li,.tabs-right>.nav-tabs>li{float:none}.tabs-left>.nav-tabs>li>a,.tabs-right>.nav-tabs>li>a{min-width:74px;margin-right:0;margin-bottom:3px}.tabs-left>.nav-tabs{float:left;margin-right:19px;border-right:1px solid #ddd}.tabs-left>.nav-tabs>li>a{margin-right:-1px;-webkit-border-radius:4px 0 0 4px;-moz-border-radius:4px 0 0 4px;border-radius:4px 0 0 4px}.tabs-left>.nav-tabs>li>a:hover{border-color:#eee #ddd #eee #eee}.tabs-left>.nav-tabs .active>a,.tabs-left>.nav-tabs .active>a:hover{border-color:#ddd transparent #ddd #ddd;*border-right-color:#fff}.tabs-right>.nav-tabs{float:right;margin-left:19px;border-left:1px solid #ddd}.tabs-right>.nav-tabs>li>a{margin-left:-1px;-webkit-border-radius:0 4px 4px 0;-moz-border-radius:0 4px 4px 0;border-radius:0 4px 4px 0}.tabs-right>.nav-tabs>li>a:hover{border-color:#eee #eee #eee #ddd}.tabs-right>.nav-tabs .active>a,.tabs-right>.nav-tabs .active>a:hover{border-color:#ddd #ddd #ddd transparent;*border-left-color:#fff}.navbar{*position:relative;*z-index:2;margin-bottom:18px;overflow:visible}.navbar-inner{min-height:40px;padding-right:20px;padding-left:20px;background-color:#2c2c2c;background-image:-moz-linear-gradient(top,#333,#222);background-image:-ms-linear-gradient(top,#333,#222);background-image:-webkit-gradient(linear,0 0,0 100%,from(#333),to(#222));background-image:-webkit-linear-gradient(top,#333,#222);background-image:-o-linear-gradient(top,#333,#222);background-image:linear-gradient(top,#333,#222);background-repeat:repeat-x;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#333333',endColorstr='#222222',GradientType=0);-webkit-box-shadow:0 1px 3px rgba(0,0,0,0.25),inset 0 -1px 0 rgba(0,0,0,0.1);-moz-box-shadow:0 1px 3px rgba(0,0,0,0.25),inset 0 -1px 0 rgba(0,0,0,0.1);box-shadow:0 1px 3px rgba(0,0,0,0.25),inset 0 -1px 0 rgba(0,0,0,0.1)}.navbar .container{width:auto}.nav-collapse.collapse{height:auto}.navbar{color:#999}.navbar .brand:hover{text-decoration:none}.navbar .brand{display:block;float:left;padding:8px 20px 12px;margin-left:-20px;font-size:20px;font-weight:200;line-height:1;color:#999}.navbar .navbar-text{margin-bottom:0;line-height:40px}.navbar .navbar-link{color:#999}.navbar .navbar-link:hover{color:#fff}.navbar .btn,.navbar .btn-group{margin-top:5px}.navbar .btn-group .btn{margin:0}.navbar-form{margin-bottom:0;*zoom:1}.navbar-form:before,.navbar-form:after{display:table;content:""}.navbar-form:after{clear:both}.navbar-form input,.navbar-form select,.navbar-form .radio,.navbar-form .checkbox{margin-top:5px}.navbar-form input,.navbar-form select{display:inline-block;margin-bottom:0}.navbar-form input[type="image"],.navbar-form input[type="checkbox"],.navbar-form input[type="radio"]{margin-top:3px}.navbar-form .input-append,.navbar-form .input-prepend{margin-top:6px;white-space:nowrap}.navbar-form .input-append input,.navbar-form .input-prepend input{margin-top:0}.navbar-search{position:relative;float:left;margin-top:6px;margin-bottom:0}.navbar-search .search-query{padding:4px 9px;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:13px;font-weight:normal;line-height:1;color:#fff;background-color:#626262;border:1px solid #151515;-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,0.1),0 1px 0 rgba(255,255,255,0.15);-moz-box-shadow:inset 0 1px 2px rgba(0,0,0,0.1),0 1px 0 rgba(255,255,255,0.15);box-shadow:inset 0 1px 2px rgba(0,0,0,0.1),0 1px 0 rgba(255,255,255,0.15);-webkit-transition:none;-moz-transition:none;-ms-transition:none;-o-transition:none;transition:none}.navbar-search .search-query:-moz-placeholder{color:#ccc}.navbar-search .search-query::-webkit-input-placeholder{color:#ccc}.navbar-search .search-query:focus,.navbar-search .search-query.focused{padding:5px 10px;color:#333;text-shadow:0 1px 0 #fff;background-color:#fff;border:0;outline:0;-webkit-box-shadow:0 0 3px rgba(0,0,0,0.15);-moz-box-shadow:0 0 3px rgba(0,0,0,0.15);box-shadow:0 0 3px rgba(0,0,0,0.15)}.navbar-fixed-top,.navbar-fixed-bottom{position:fixed;right:0;left:0;z-index:1030;margin-bottom:0}.navbar-fixed-top .navbar-inner,.navbar-fixed-bottom .navbar-inner{padding-right:0;padding-left:0;-webkit-border-radius:0;-moz-border-radius:0;border-radius:0}.navbar-fixed-top .container,.navbar-fixed-bottom .container{width:940px}.navbar-fixed-top{top:0}.navbar-fixed-bottom{bottom:0}.navbar .nav{position:relative;left:0;display:block;float:left;margin:0 10px 0 0}.navbar .nav.pull-right{float:right}.navbar .nav>li{display:block;float:left}.navbar .nav>li>a{float:none;padding:9px 10px 11px;line-height:19px;color:#999;text-decoration:none;text-shadow:0 -1px 0 rgba(0,0,0,0.25)}.navbar .btn{display:inline-block;padding:4px 10px 4px;margin:5px 5px 6px;line-height:18px}.navbar .btn-group{padding:5px 5px 6px;margin:0}.navbar .nav>li>a:hover{color:#fff;text-decoration:none;background-color:transparent}.navbar .nav .active>a,.navbar .nav .active>a:hover{color:#fff;text-decoration:none;background-color:#222}.navbar .divider-vertical{width:1px;height:40px;margin:0 9px;overflow:hidden;background-color:#222;border-right:1px solid #333}.navbar .nav.pull-right{margin-right:0;margin-left:10px}.navbar .btn-navbar{display:none;float:right;padding:7px 10px;margin-right:5px;margin-left:5px;background-color:#2c2c2c;*background-color:#222;background-image:-ms-linear-gradient(top,#333,#222);background-image:-webkit-gradient(linear,0 0,0 100%,from(#333),to(#222));background-image:-webkit-linear-gradient(top,#333,#222);background-image:-o-linear-gradient(top,#333,#222);background-image:linear-gradient(top,#333,#222);background-image:-moz-linear-gradient(top,#333,#222);background-repeat:repeat-x;border-color:#222 #222 #000;border-color:rgba(0,0,0,0.1) rgba(0,0,0,0.1) rgba(0,0,0,0.25);filter:progid:dximagetransform.microsoft.gradient(startColorstr='#333333',endColorstr='#222222',GradientType=0);filter:progid:dximagetransform.microsoft.gradient(enabled=false);-webkit-box-shadow:inset 0 1px 0 rgba(255,255,255,0.1),0 1px 0 rgba(255,255,255,0.075);-moz-box-shadow:inset 0 1px 0 rgba(255,255,255,0.1),0 1px 0 rgba(255,255,255,0.075);box-shadow:inset 0 1px 0 rgba(255,255,255,0.1),0 1px 0 rgba(255,255,255,0.075)}.navbar .btn-navbar:hover,.navbar .btn-navbar:active,.navbar .btn-navbar.active,.navbar .btn-navbar.disabled,.navbar .btn-navbar[disabled]{background-color:#222;*background-color:#151515}.navbar .btn-navbar:active,.navbar .btn-navbar.active{background-color:#080808 \9}.navbar .btn-navbar .icon-bar{display:block;width:18px;height:2px;background-color:#f5f5f5;-webkit-border-radius:1px;-moz-border-radius:1px;border-radius:1px;-webkit-box-shadow:0 1px 0 rgba(0,0,0,0.25);-moz-box-shadow:0 1px 0 rgba(0,0,0,0.25);box-shadow:0 1px 0 rgba(0,0,0,0.25)}.btn-navbar .icon-bar+.icon-bar{margin-top:3px}.navbar .dropdown-menu:before{position:absolute;top:-7px;left:9px;display:inline-block;border-right:7px solid transparent;border-bottom:7px solid #ccc;border-left:7px solid transparent;border-bottom-color:rgba(0,0,0,0.2);content:''}.navbar .dropdown-menu:after{position:absolute;top:-6px;left:10px;display:inline-block;border-right:6px solid transparent;border-bottom:6px solid #fff;border-left:6px solid transparent;content:''}.navbar-fixed-bottom .dropdown-menu:before{top:auto;bottom:-7px;border-top:7px solid #ccc;border-bottom:0;border-top-color:rgba(0,0,0,0.2)}.navbar-fixed-bottom .dropdown-menu:after{top:auto;bottom:-6px;border-top:6px solid #fff;border-bottom:0}.navbar .nav li.dropdown .dropdown-toggle .caret,.navbar .nav li.dropdown.open .caret{border-top-color:#fff;border-bottom-color:#fff}.navbar .nav li.dropdown.active .caret{opacity:1;filter:alpha(opacity=100)}.navbar .nav li.dropdown.open>.dropdown-toggle,.navbar .nav li.dropdown.active>.dropdown-toggle,.navbar .nav li.dropdown.open.active>.dropdown-toggle{background-color:transparent}.navbar .nav li.dropdown.active>.dropdown-toggle:hover{color:#fff}.navbar .pull-right .dropdown-menu,.navbar .dropdown-menu.pull-right{right:0;left:auto}.navbar .pull-right .dropdown-menu:before,.navbar .dropdown-menu.pull-right:before{right:12px;left:auto}.navbar .pull-right .dropdown-menu:after,.navbar .dropdown-menu.pull-right:after{right:13px;left:auto}.breadcrumb{padding:7px 14px;margin:0 0 18px;list-style:none;background-color:#fbfbfb;background-image:-moz-linear-gradient(top,#fff,#f5f5f5);background-image:-ms-linear-gradient(top,#fff,#f5f5f5);background-image:-webkit-gradient(linear,0 0,0 100%,from(#fff),to(#f5f5f5));background-image:-webkit-linear-gradient(top,#fff,#f5f5f5);background-image:-o-linear-gradient(top,#fff,#f5f5f5);background-image:linear-gradient(top,#fff,#f5f5f5);background-repeat:repeat-x;border:1px solid #ddd;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#ffffff',endColorstr='#f5f5f5',GradientType=0);-webkit-box-shadow:inset 0 1px 0 #fff;-moz-box-shadow:inset 0 1px 0 #fff;box-shadow:inset 0 1px 0 #fff}.breadcrumb li{display:inline-block;*display:inline;text-shadow:0 1px 0 #fff;*zoom:1}.breadcrumb .divider{padding:0 5px;color:#999}.breadcrumb .active a{color:#333}.pagination{height:36px;margin:18px 0}.pagination ul{display:inline-block;*display:inline;margin-bottom:0;margin-left:0;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;*zoom:1;-webkit-box-shadow:0 1px 2px rgba(0,0,0,0.05);-moz-box-shadow:0 1px 2px rgba(0,0,0,0.05);box-shadow:0 1px 2px rgba(0,0,0,0.05)}.pagination li{display:inline}.pagination a{float:left;padding:0 14px;line-height:34px;text-decoration:none;border:1px solid #ddd;border-left-width:0}.pagination a:hover,.pagination .active a{background-color:#f5f5f5}.pagination .active a{color:#999;cursor:default}.pagination .disabled span,.pagination .disabled a,.pagination .disabled a:hover{color:#999;cursor:default;background-color:transparent}.pagination li:first-child a{border-left-width:1px;-webkit-border-radius:3px 0 0 3px;-moz-border-radius:3px 0 0 3px;border-radius:3px 0 0 3px}.pagination li:last-child a{-webkit-border-radius:0 3px 3px 0;-moz-border-radius:0 3px 3px 0;border-radius:0 3px 3px 0}.pagination-centered{text-align:center}.pagination-right{text-align:right}.pager{margin-bottom:18px;margin-left:0;text-align:center;list-style:none;*zoom:1}.pager:before,.pager:after{display:table;content:""}.pager:after{clear:both}.pager li{display:inline}.pager a{display:inline-block;padding:5px 14px;background-color:#fff;border:1px solid #ddd;-webkit-border-radius:15px;-moz-border-radius:15px;border-radius:15px}.pager a:hover{text-decoration:none;background-color:#f5f5f5}.pager .next a{float:right}.pager .previous a{float:left}.pager .disabled a,.pager .disabled a:hover{color:#999;cursor:default;background-color:#fff}.modal-open .dropdown-menu{z-index:2050}.modal-open .dropdown.open{*z-index:2050}.modal-open .popover{z-index:2060}.modal-open .tooltip{z-index:2070}.modal-backdrop{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1040;background-color:#000}.modal-backdrop.fade{opacity:0}.modal-backdrop,.modal-backdrop.fade.in{opacity:.8;filter:alpha(opacity=80)}.modal{position:fixed;top:50%;left:50%;z-index:1050;width:560px;margin:-250px 0 0 -280px;overflow:auto;background-color:#fff;border:1px solid #999;border:1px solid rgba(0,0,0,0.3);*border:1px solid #999;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow:0 3px 7px rgba(0,0,0,0.3);-moz-box-shadow:0 3px 7px rgba(0,0,0,0.3);box-shadow:0 3px 7px rgba(0,0,0,0.3);-webkit-background-clip:padding-box;-moz-background-clip:padding-box;background-clip:padding-box}.modal.fade{top:-25%;-webkit-transition:opacity .3s linear,top .3s ease-out;-moz-transition:opacity .3s linear,top .3s ease-out;-ms-transition:opacity .3s linear,top .3s ease-out;-o-transition:opacity .3s linear,top .3s ease-out;transition:opacity .3s linear,top .3s ease-out}.modal.fade.in{top:50%}.modal-header{padding:9px 15px;border-bottom:1px solid #eee}.modal-header .close{margin-top:2px}.modal-body{max-height:400px;padding:15px;overflow-y:auto}.modal-form{margin-bottom:0}.modal-footer{padding:14px 15px 15px;margin-bottom:0;text-align:right;background-color:#f5f5f5;border-top:1px solid #ddd;-webkit-border-radius:0 0 6px 6px;-moz-border-radius:0 0 6px 6px;border-radius:0 0 6px 6px;*zoom:1;-webkit-box-shadow:inset 0 1px 0 #fff;-moz-box-shadow:inset 0 1px 0 #fff;box-shadow:inset 0 1px 0 #fff}.modal-footer:before,.modal-footer:after{display:table;content:""}.modal-footer:after{clear:both}.modal-footer .btn+.btn{margin-bottom:0;margin-left:5px}.modal-footer .btn-group .btn+.btn{margin-left:-1px}.tooltip{position:absolute;z-index:1020;display:block;padding:5px;font-size:11px;opacity:0;filter:alpha(opacity=0);visibility:visible}.tooltip.in{opacity:.8;filter:alpha(opacity=80)}.tooltip.top{margin-top:-2px}.tooltip.right{margin-left:2px}.tooltip.bottom{margin-top:2px}.tooltip.left{margin-left:-2px}.tooltip.top .tooltip-arrow{bottom:0;left:50%;margin-left:-5px;border-top:5px solid #000;border-right:5px solid transparent;border-left:5px solid transparent}.tooltip.left .tooltip-arrow{top:50%;right:0;margin-top:-5px;border-top:5px solid transparent;border-bottom:5px solid transparent;border-left:5px solid #000}.tooltip.bottom .tooltip-arrow{top:0;left:50%;margin-left:-5px;border-right:5px solid transparent;border-bottom:5px solid #000;border-left:5px solid transparent}.tooltip.right .tooltip-arrow{top:50%;left:0;margin-top:-5px;border-top:5px solid transparent;border-right:5px solid #000;border-bottom:5px solid transparent}.tooltip-inner{max-width:200px;padding:3px 8px;color:#fff;text-align:center;text-decoration:none;background-color:#000;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.tooltip-arrow{position:absolute;width:0;height:0}.popover{position:absolute;top:0;left:0;z-index:1010;display:none;padding:5px}.popover.top{margin-top:-5px}.popover.right{margin-left:5px}.popover.bottom{margin-top:5px}.popover.left{margin-left:-5px}.popover.top .arrow{bottom:0;left:50%;margin-left:-5px;border-top:5px solid #000;border-right:5px solid transparent;border-left:5px solid transparent}.popover.right .arrow{top:50%;left:0;margin-top:-5px;border-top:5px solid transparent;border-right:5px solid #000;border-bottom:5px solid transparent}.popover.bottom .arrow{top:0;left:50%;margin-left:-5px;border-right:5px solid transparent;border-bottom:5px solid #000;border-left:5px solid transparent}.popover.left .arrow{top:50%;right:0;margin-top:-5px;border-top:5px solid transparent;border-bottom:5px solid transparent;border-left:5px solid #000}.popover .arrow{position:absolute;width:0;height:0}.popover-inner{width:280px;padding:3px;overflow:hidden;background:#000;background:rgba(0,0,0,0.8);-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px;-webkit-box-shadow:0 3px 7px rgba(0,0,0,0.3);-moz-box-shadow:0 3px 7px rgba(0,0,0,0.3);box-shadow:0 3px 7px rgba(0,0,0,0.3)}.popover-title{padding:9px 15px;line-height:1;background-color:#f5f5f5;border-bottom:1px solid #eee;-webkit-border-radius:3px 3px 0 0;-moz-border-radius:3px 3px 0 0;border-radius:3px 3px 0 0}.popover-content{padding:14px;background-color:#fff;-webkit-border-radius:0 0 3px 3px;-moz-border-radius:0 0 3px 3px;border-radius:0 0 3px 3px;-webkit-background-clip:padding-box;-moz-background-clip:padding-box;background-clip:padding-box}.popover-content p,.popover-content ul,.popover-content ol{margin-bottom:0}.thumbnails{margin-left:-20px;list-style:none;*zoom:1}.thumbnails:before,.thumbnails:after{display:table;content:""}.thumbnails:after{clear:both}.row-fluid .thumbnails{margin-left:0}.thumbnails>li{float:left;margin-bottom:18px;margin-left:20px}.thumbnail{display:block;padding:4px;line-height:1;border:1px solid #ddd;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;-webkit-box-shadow:0 1px 1px rgba(0,0,0,0.075);-moz-box-shadow:0 1px 1px rgba(0,0,0,0.075);box-shadow:0 1px 1px rgba(0,0,0,0.075)}a.thumbnail:hover{border-color:#08c;-webkit-box-shadow:0 1px 4px rgba(0,105,214,0.25);-moz-box-shadow:0 1px 4px rgba(0,105,214,0.25);box-shadow:0 1px 4px rgba(0,105,214,0.25)}.thumbnail>img{display:block;max-width:100%;margin-right:auto;margin-left:auto}.thumbnail .caption{padding:9px}.label,.badge{font-size:10.998px;font-weight:bold;line-height:14px;color:#fff;text-shadow:0 -1px 0 rgba(0,0,0,0.25);white-space:nowrap;vertical-align:baseline;background-color:#999}.label{padding:1px 4px 2px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px}.badge{padding:1px 9px 2px;-webkit-border-radius:9px;-moz-border-radius:9px;border-radius:9px}a.label:hover,a.badge:hover{color:#fff;text-decoration:none;cursor:pointer}.label-important,.badge-important{background-color:#b94a48}.label-important[href],.badge-important[href]{background-color:#953b39}.label-warning,.badge-warning{background-color:#f89406}.label-warning[href],.badge-warning[href]{background-color:#c67605}.label-success,.badge-success{background-color:#468847}.label-success[href],.badge-success[href]{background-color:#356635}.label-info,.badge-info{background-color:#3a87ad}.label-info[href],.badge-info[href]{background-color:#2d6987}.label-inverse,.badge-inverse{background-color:#333}.label-inverse[href],.badge-inverse[href]{background-color:#1a1a1a}@-webkit-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@-moz-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@-ms-keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}@-o-keyframes progress-bar-stripes{from{background-position:0 0}to{background-position:40px 0}}@keyframes progress-bar-stripes{from{background-position:40px 0}to{background-position:0 0}}.progress{height:18px;margin-bottom:18px;overflow:hidden;background-color:#f7f7f7;background-image:-moz-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-ms-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-webkit-gradient(linear,0 0,0 100%,from(#f5f5f5),to(#f9f9f9));background-image:-webkit-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-o-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:linear-gradient(top,#f5f5f5,#f9f9f9);background-repeat:repeat-x;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#f5f5f5',endColorstr='#f9f9f9',GradientType=0);-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,0.1);-moz-box-shadow:inset 0 1px 2px rgba(0,0,0,0.1);box-shadow:inset 0 1px 2px rgba(0,0,0,0.1)}.progress .bar{width:0;height:18px;font-size:12px;color:#fff;text-align:center;text-shadow:0 -1px 0 rgba(0,0,0,0.25);background-color:#0e90d2;background-image:-moz-linear-gradient(top,#149bdf,#0480be);background-image:-webkit-gradient(linear,0 0,0 100%,from(#149bdf),to(#0480be));background-image:-webkit-linear-gradient(top,#149bdf,#0480be);background-image:-o-linear-gradient(top,#149bdf,#0480be);background-image:linear-gradient(top,#149bdf,#0480be);background-image:-ms-linear-gradient(top,#149bdf,#0480be);background-repeat:repeat-x;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#149bdf',endColorstr='#0480be',GradientType=0);-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,0.15);-moz-box-shadow:inset 0 -1px 0 rgba(0,0,0,0.15);box-shadow:inset 0 -1px 0 rgba(0,0,0,0.15);-webkit-box-sizing:border-box;-moz-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;-webkit-transition:width .6s ease;-moz-transition:width .6s ease;-ms-transition:width .6s ease;-o-transition:width .6s ease;transition:width .6s ease}.progress-striped .bar{background-color:#149bdf;background-image:-o-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(0.25,rgba(255,255,255,0.15)),color-stop(0.25,transparent),color-stop(0.5,transparent),color-stop(0.5,rgba(255,255,255,0.15)),color-stop(0.75,rgba(255,255,255,0.15)),color-stop(0.75,transparent),to(transparent));background-image:linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);-webkit-background-size:40px 40px;-moz-background-size:40px 40px;-o-background-size:40px 40px;background-size:40px 40px}.progress.active .bar{-webkit-animation:progress-bar-stripes 2s linear infinite;-moz-animation:progress-bar-stripes 2s linear infinite;-ms-animation:progress-bar-stripes 2s linear infinite;-o-animation:progress-bar-stripes 2s linear infinite;animation:progress-bar-stripes 2s linear infinite}.progress-danger .bar{background-color:#dd514c;background-image:-moz-linear-gradient(top,#ee5f5b,#c43c35);background-image:-ms-linear-gradient(top,#ee5f5b,#c43c35);background-image:-webkit-gradient(linear,0 0,0 100%,from(#ee5f5b),to(#c43c35));background-image:-webkit-linear-gradient(top,#ee5f5b,#c43c35);background-image:-o-linear-gradient(top,#ee5f5b,#c43c35);background-image:linear-gradient(top,#ee5f5b,#c43c35);background-repeat:repeat-x;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#ee5f5b',endColorstr='#c43c35',GradientType=0)}.progress-danger.progress-striped .bar{background-color:#ee5f5b;background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(0.25,rgba(255,255,255,0.15)),color-stop(0.25,transparent),color-stop(0.5,transparent),color-stop(0.5,rgba(255,255,255,0.15)),color-stop(0.75,rgba(255,255,255,0.15)),color-stop(0.75,transparent),to(transparent));background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent)}.progress-success .bar{background-color:#5eb95e;background-image:-moz-linear-gradient(top,#62c462,#57a957);background-image:-ms-linear-gradient(top,#62c462,#57a957);background-image:-webkit-gradient(linear,0 0,0 100%,from(#62c462),to(#57a957));background-image:-webkit-linear-gradient(top,#62c462,#57a957);background-image:-o-linear-gradient(top,#62c462,#57a957);background-image:linear-gradient(top,#62c462,#57a957);background-repeat:repeat-x;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#62c462',endColorstr='#57a957',GradientType=0)}.progress-success.progress-striped .bar{background-color:#62c462;background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(0.25,rgba(255,255,255,0.15)),color-stop(0.25,transparent),color-stop(0.5,transparent),color-stop(0.5,rgba(255,255,255,0.15)),color-stop(0.75,rgba(255,255,255,0.15)),color-stop(0.75,transparent),to(transparent));background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent)}.progress-info .bar{background-color:#4bb1cf;background-image:-moz-linear-gradient(top,#5bc0de,#339bb9);background-image:-ms-linear-gradient(top,#5bc0de,#339bb9);background-image:-webkit-gradient(linear,0 0,0 100%,from(#5bc0de),to(#339bb9));background-image:-webkit-linear-gradient(top,#5bc0de,#339bb9);background-image:-o-linear-gradient(top,#5bc0de,#339bb9);background-image:linear-gradient(top,#5bc0de,#339bb9);background-repeat:repeat-x;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#5bc0de',endColorstr='#339bb9',GradientType=0)}.progress-info.progress-striped .bar{background-color:#5bc0de;background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(0.25,rgba(255,255,255,0.15)),color-stop(0.25,transparent),color-stop(0.5,transparent),color-stop(0.5,rgba(255,255,255,0.15)),color-stop(0.75,rgba(255,255,255,0.15)),color-stop(0.75,transparent),to(transparent));background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent)}.progress-warning .bar{background-color:#faa732;background-image:-moz-linear-gradient(top,#fbb450,#f89406);background-image:-ms-linear-gradient(top,#fbb450,#f89406);background-image:-webkit-gradient(linear,0 0,0 100%,from(#fbb450),to(#f89406));background-image:-webkit-linear-gradient(top,#fbb450,#f89406);background-image:-o-linear-gradient(top,#fbb450,#f89406);background-image:linear-gradient(top,#fbb450,#f89406);background-repeat:repeat-x;filter:progid:dximagetransform.microsoft.gradient(startColorstr='#fbb450',endColorstr='#f89406',GradientType=0)}.progress-warning.progress-striped .bar{background-color:#fbb450;background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(0.25,rgba(255,255,255,0.15)),color-stop(0.25,transparent),color-stop(0.5,transparent),color-stop(0.5,rgba(255,255,255,0.15)),color-stop(0.75,rgba(255,255,255,0.15)),color-stop(0.75,transparent),to(transparent));background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent);background-image:linear-gradient(-45deg,rgba(255,255,255,0.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,0.15) 50%,rgba(255,255,255,0.15) 75%,transparent 75%,transparent)}.accordion{margin-bottom:18px}.accordion-group{margin-bottom:2px;border:1px solid #e5e5e5;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.accordion-heading{border-bottom:0}.accordion-heading .accordion-toggle{display:block;padding:8px 15px}.accordion-toggle{cursor:pointer}.accordion-inner{padding:9px 15px;border-top:1px solid #e5e5e5}.carousel{position:relative;margin-bottom:18px;line-height:1}.carousel-inner{position:relative;width:100%;overflow:hidden}.carousel .item{position:relative;display:none;-webkit-transition:.6s ease-in-out left;-moz-transition:.6s ease-in-out left;-ms-transition:.6s ease-in-out left;-o-transition:.6s ease-in-out left;transition:.6s ease-in-out left}.carousel .item>img{display:block;line-height:1}.carousel .active,.carousel .next,.carousel .prev{display:block}.carousel .active{left:0}.carousel .next,.carousel .prev{position:absolute;top:0;width:100%}.carousel .next{left:100%}.carousel .prev{left:-100%}.carousel .next.left,.carousel .prev.right{left:0}.carousel .active.left{left:-100%}.carousel .active.right{left:100%}.carousel-control{position:absolute;top:40%;left:15px;width:40px;height:40px;margin-top:-20px;font-size:60px;font-weight:100;line-height:30px;color:#fff;text-align:center;background:#222;border:3px solid #fff;-webkit-border-radius:23px;-moz-border-radius:23px;border-radius:23px;opacity:.5;filter:alpha(opacity=50)}.carousel-control.right{right:15px;left:auto}.carousel-control:hover{color:#fff;text-decoration:none;opacity:.9;filter:alpha(opacity=90)}.carousel-caption{position:absolute;right:0;bottom:0;left:0;padding:10px 15px 5px;background:#333;background:rgba(0,0,0,0.75)}.carousel-caption h4,.carousel-caption p{color:#fff}.hero-unit{padding:60px;margin-bottom:30px;background-color:#eee;-webkit-border-radius:6px;-moz-border-radius:6px;border-radius:6px}.hero-unit h1{margin-bottom:0;font-size:60px;line-height:1;letter-spacing:-1px;color:inherit}.hero-unit p{font-size:18px;font-weight:200;line-height:27px;color:inherit}.pull-right{float:right}.pull-left{float:left}.hide{display:none}.show{display:block}.invisible{visibility:hidden}
        </style>
css;
        ?>
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
        </div>
    </body>
</html>