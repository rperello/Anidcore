<nav id="breadcrumbsbar">
    <ul class="breadcrumb">
        <li>
            <a href="<?php echo AC_CMS_URL; ?>pages/">Páginas</a> <span class="divider">/</span>
        </li>
        <li>
            <?php echo $record->name ?>
        </li>
    </ul>
</nav>

<div class="page-header">
    <?php if($is_new): ?>
    <h1>Nueva página</h1>
    <?php else: ?>
    <h1>Editar página <small><?php echo $record->name ?></small></h1>
    <?php endif; ?>
</div>

<div class="tabbable tabs-horizontal">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab1" data-toggle="tab">General</a></li>
        <li><a href="#tab2" data-toggle="tab">Imágenes</a></li>
        <li><a href="#tab3" data-toggle="tab">SEO</a></li>
        <li><a href="#tab4" data-toggle="tab">Avanzado</a></li>
        <li><a href="#tab5" data-toggle="tab">Desarrollador</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab1">
<?php  
 ac_ckeditor()->editor("editor1", "<p>Initial value.</p>");
?>

        </div>
        <div class="tab-pane" id="tab2">Imágenes
        </div>
        <div class="tab-pane" id="tab3">SEO
        </div>
        <div class="tab-pane" id="tab4">Avanzado
        </div>
        <div class="tab-pane" id="tab5">Desarrollador
        </div>
    </div>
</div>

<footer id="footer">
    <div id="footer_content" class="container-fluid">
        <div class="pull-right">
            <a href="javascript:;" class="btn btn-i btn-inverse"><i class="icon-share-alt icon-white"></i> Abrir</a>
            <a href="javascript:;" class="btn btn-i btn-success"><?php echo __t("Save"); ?></a>
        </div>
        <div class="clear"></div>
    </div>
</footer>