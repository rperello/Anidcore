<div id="admintoolbar" class="navbar navbar-fixed-top">
    <div id="admintoolbar_wrapper" class="navbar-inner">
        <div id="admintoolbar_content" class="">
            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="dropdown active_">
                        <a id="admintoolbar_logo" class="dropdown-toggle" href="javascript:;<?php //echo App::admin()->url(); ?>">
                            <img src="<?php echo App::logo("app") ? App::logo("app") : App::logo("admin");  ?>" />
                        </a>
                        <ul class="dropdown-menu">
                            <li<?php if(App::is("app")) echo ' class="active" '; ?>>
                                <a href="<?php echo App::url("dir"); ?>">
                                    <i class="icon-home<?php if(App::is("app")) echo ' icon-white '; ?>"></i> <?php echo __t("Home"); ?>
                                </a>
                            </li>
                            <li<?php if(App::is("admin")) echo ' class="active" '; ?>>
                                <a href="<?php echo App::admin()->url(); ?>">
                                    <i class="icon-briefcase<?php if(App::is("admin")) echo ' icon-white '; ?>"></i> <?php echo __t("Admin Panel"); ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li><a href="#"><i class="icon-envelope"></i> <?php echo __t("Web mail"); ?></a></li>
                            <li><a href="#"><i class="icon-user"></i> <?php echo __t("Server admin"); ?></a></li>
                            <li>
                                <a href="mailto:contact@example.com?subject=Support&body=Ref:<?php echo urlencode(" ".App::url("current")); ?>">
                                    <i class="icon-question-sign"></i>
                                     <?php echo __t("Support"); ?>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <form class="navbar-search">
                        <input name="acq" type="text" class="search-query span2" placeholder="<?php echo __t("Search"); ?>" />
                        </form>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown_" href="<?php echo App::admin()->url(); ?>documents/">
                            <?php echo __t("Pages"); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo App::admin()->url(); ?>documents/new"><i class="icon-plus"></i> <?php echo __t("New page"); ?></a></li>
                            <li class="divider"></li>
                            <?php
                            
                                $pages = R_Document::find("(type='page') AND (is_page=1) AND (parent_fk is null)", "sort_order");
                                foreach($pages as $i => $p){
                                    ?>
                            <li><a href="<?php echo App::admin()->url()."documents/?id=".$p->id; ?>"><em> <?php echo $p->name; ?></em></a></li>
                            <?php
                                    if($i==1){
                                        ?>
                                <li><a href="<?php echo App::admin()->url()."documents/"; ?>"> &raquo; ver más </a></li>
                                <?php
                                        break;
                                    }
                                }
                            ?>
                        </ul>
                    </li>
                    
                    <?php
                    
//                    Ac_Cms::registerToolbarItem("m1", '<li><a href="#">Test 1</a></li>',2);
//                    Ac_Cms::registerToolbarItem("m2", '<li><a href="#">Test 2</a></li>');
                    //echo Ac_Cms::toolbarItemsHTML();
                    
                    ?>
                 
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                             <?php echo __t("Appearance"); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><?php echo __t("Menus"); ?></a></li>
                            <li><a href="#"><?php echo __t("Widgets"); ?></a></li>
                            <li class="divider"></li>
                            <li><a href="#"><?php echo __t("Themes"); ?></a></li>
                            <li><a href="#"><?php echo __t("Modules"); ?></a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo App::admin()->url(); ?>filesystem/"><?php echo __t("Code editor"); ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <?php echo __t("System"); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><?php echo __t("Languages"); ?></a></li>
                            <li><a href="#"><?php echo __t("Static texts"); ?></a></li>
                            <li class="divider"></li>
                            <li><a href="#"><i class="icon-cog"></i> <?php echo __t("Preferences"); ?></a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav pull-right">
                    <li class="dropdown" data-toggle="tooltip">
                        <a class="dropdown-toggle" href="#" data-toggle="dropdown" title="<?php echo __t("Users"); ?>"><i class="icon-user icon-white"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><i class="icon-plus"></i> Nuevo usuario</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Usuarios web</a></li>
                            <li><a href="#">Administradores</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Grupos</a></li>
                            <li><a href="#">Privilegios</a></li>
                            <li class="divider"></li>
                            <li><a href="#">Mi cuenta</a></li>
                            <li><a href="#"><i class="icon-off"></i> <?php echo __t("Logout"); ?></a></li>
                        </ul>
                    </li>
                    <?php if(/*ac_is_document()*/ false): ?>
                    <li>
                        <a title="Editar esta página" href="<?php echo App::admin()->url()."documents/?id=".ac_document()->id; ?>" class="pull-right">
                            <i class="icon-pencil icon-white"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li id="notifier_area" class="active">
                        <a id="notifier_counter" href="#" title="Tienes 21 notificaciones">21</a>
                        <div id="notifier_box" style="display:none">lorem ipsum</div>
                    </li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>