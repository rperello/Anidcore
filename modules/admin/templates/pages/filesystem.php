<?php
$readerr = false;
if (isset($_POST["filecontent"]) && isset($_POST["filename"])) {
    $filename = ac_post("filename");
    $filecontent = str_replace(array('&l' . 't;', '&g' . 't;'), array("<", ">"), $_POST["filecontent"]);
    if (!is_readable($filename)){
        $filecontent="";
        try {
            touch($filename);
        } catch (Exception $exc) {
            $readerr = true;
        }
    }
    if ($readerr == false) {
        file_put_contents($filename, $filecontent);
    }
}


$fileenc = ac_get("f", base64_encode(ac_post("filename", ac_module("app")->templatesPath() . "home.php")));
$filename = base64_decode($fileenc);

$pathenc = ac_get("p",base64_encode(dirname($filename)));
$path = base64_decode($pathenc);

$breadcrumbs = str_replace(AC_PATH, "", $path);
$breadcrumbs = explode(_DS, trim($breadcrumbs, " "._DS));
?>

<div class="page-header">
    <h1>Editor de código <small><?php echo str_replace(AC_PATH, "", base64_decode($fileenc)); ?></small></h1>
</div>
<nav id="breadcrumbsbar_">
    <ul class="breadcrumb">
        <li style="margin-right: 10px"><i class="icon-folder-open"></i></li>
        <?php
        $curr="";
        foreach($breadcrumbs as $i => $folder){
            $curr = $curr._DS.$folder;
            if( ($i+1) == count($breadcrumbs) ){
                ?>
        
        <li class="active">
            <?php echo $folder; ?>
        </li>
        <?php
            }else{
                ?>
        <li>
            <a href="<?php echo ac_url(); ?>filesystem/?p=<?php echo base64_encode(AC_PATH.$curr._DS); ?>"><?php echo $folder; ?></a> <span class="divider">/</span>
        </li>
        <?php
            }
        }
        
        ?>
    </ul>
</nav>

<div class="flexview" data-cols="2">
    <div class="flexview-1">
        <div id="filetree" class="sidebar-nav">
            <?php
            echo file_tree($path, ac_module()->theme(), ac_url() . "filesystem/?f=[link]", array("txt", "htaccess", "html", "css", "scss", "js", "php", "ini", "sql", "json", "xml", "sh"));
            ?>
        </div>
    </div>
<?php
$contents = "";

if ($fileenc):
    ?>

        <?php

        if (isset($_GET["delete"]) && is_readable($filename)) {
            unlink($filename);
            echo '<script>window.location.href="'.Ac_Router::actionUrl().'";</script>';
        }
        if (is_readable($filename)) {
            $contents = str_replace(array("<", ">"), array('&l' . 't;', '&g' . 't;'), file_get_contents($filename));
            ?>
            <?php
        }
        ?>

    <?php endif; ?>
    <div class="flexview-2">
        <form style="margin-left:20px;" method="post" class="wells" action="<?php echo ac_url(); ?>filesystem/?f=<?php echo $fileenc; ?>">
<!--            <h3><?php echo basename($filename); ?></h3>-->
            <div id="codeeditor" class="code-editor"><?php echo $contents; ?></div>
            <textarea name="filecontent" style="display:none;"><?php echo $contents; ?></textarea>
            <input name="filename" class="content-box-sizing" value="<?php echo $filename; ?>" style="width:99%" />
            <button type="submit" class="btn btn-success pull-right">Guardar</button>
            <a onclick="return window.confirm('¿Estás seguro que deseas eliminar este archivo?');" href="<?php echo ac_url(); ?>filesystem/?f=<?php echo $fileenc; ?>&delete" class="btn btn-danger">Eliminar</a>
            <div class="clear"></div>
        </form>
    </div>
</div>


<style>

    .code-editor{
        width:99%;
        height:500px;
        font-family: 'Courier New', monospace;
        color:#000;
        display:none;
        background: #fff;
    }
    #filetree .php-file-tree{
        max-height: 500px;
    }
    .ace_editor{
        position: relative;
        display:block;
        margin-bottom:20px;
        border:1px solid #ccc;
        font-size:14px;
    }
    .ace_print_margin{
        display:none;
    }
</style>


<?php
$ext = file_extension($filename);
switch ($ext) {
    case "php":
    case "phpt": $acemode = "php";
        break;
    case "js": $acemode = "javascript";
        break;
    case "html": $acemode = "html";
        break;
    case "css": $acemode = "css";
        break;
    case "json": $acemode = "json";
        break;
    case "scss": $acemode = "scss";
        break;
    case "xml": $acemode = "xml";
        break;
    default: $acemode = "";
        break;
}
?>
<?php $theme="solarized_dark" ?>
<script src="<?php echo ac_url("media"); ?>js/ace/src/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo ac_url("media"); ?>js/ace/src/theme-<?php echo $theme; ?>.js" type="text/javascript" charset="utf-8"></script>
<?php if (!empty($acemode)): ?><script src="<?php echo ac_url("media"); ?>js/ace/src/mode-<?php echo $acemode; ?>.js" type="text/javascript" charset="utf-8"></script><?php endif; ?>
<script>
    $(window).load(function(){
        var ed = ace.edit("codeeditor");
        ed.setTheme("ace/theme/<?php echo $theme; ?>");
    
        var ed_textarea = $('textarea[name="filecontent"]').hide();

        //ed.getSession().setValue(ed_textarea.val());
        ed.getSession().on('change', function(){
            ed_textarea.val(ed.getSession().getValue());
        });
    
<?php if (!empty($acemode)): ?>
                var aceMode = require("ace/mode/<?php echo $acemode; ?>").Mode;
                ed.getSession().setMode(new aceMode());
<?php endif; ?>
        
            $(".pft-file[data-filename='<?php echo $fileenc; ?>']").addClass("active").show().parents().show();
        });
</script>