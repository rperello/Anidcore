

<div class="page-header">
    <h1>Páginas</h1>
</div>

<table class="table table-bordered table-striped" style="background: #fff;">
    <thead>
        <tr>
            <th style="width: 70px;">#</th>
            <th>Title</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($records as $r): /*@var $r R_Page*/  $edit_url = ac_module()->url()."documents/?id=".$r->id ; ?>
        <tr>
            <td><?php echo $r->id; ?></td>
            <td><a href="<?php echo $edit_url; ?>"><?php echo $r->name; ?></a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<footer id="footer">
    <div id="footer_content" class="container-fluid">
        <div class="pull-right">
            <a href="javascript:;" class="btn btn-i btn-success"><?php echo __t("Save"); ?></a>
        </div>
        <div class="clear"></div>
    </div>
</footer>