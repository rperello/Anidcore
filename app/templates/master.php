<!DOCTYPE html>
<html lang="<?php //echo App::language()->code; ?>" data-document="<?php //echo App::document()->id; ?>" class="admintoolbar-visible- <?php //echo App::htmlClasses($template_name) ?>">
    <head>
        <?php include "head.php"; ?>
    </head>
    <body>
        <div id="wrapper">
            <?php //include Ac::module("admin")->templatesPath()."toolbar.php"; ?>
            <?php include "header.php"; ?>
            <div id="main">
                <div id="main_wrapper">
                    <div id="main_content" class="container">
                        <?php include $template_file; ?>
                    </div>
                </div>
            </div>
            <?php include "footer.php"; ?>
        </div>
    </body>
</html>