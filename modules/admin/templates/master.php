<!DOCTYPE html>
<html lang="en" class="admintoolbar-visible <?php //echo App::htmlClasses($template_name); ?>">
    <head>
        <?php include "head.php"; ?>
    </head>
    <body>
        <div id="wrapper">
            <?php include "toolbar.php"; ?>
            <?php include "header.php"; ?>
            <div id="main" class="container-fluid">
                <div id="main_wrapper">
                    <div id="main_content">
                        <?php include $template_file; ?>
                    </div>
                </div>
            </div>
            <?php //include "footer.php"; ?>
        </div>
    </body>
</html>