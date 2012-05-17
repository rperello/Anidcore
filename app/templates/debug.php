<div class="row ac-debug">
    <div class="span6">
        <h2>Request</h2>
        <table class="table table-striped table-condensed">
        <?php
        $vars = Ac::context()->toArray();
        foreach($vars as $i => $v){
            ?>
            <tr>
                <th><?php echo $i; ?></th>
                <td><?php 
                    if($v===true) echo '<em>true</em>';
                    elseif($v===false) echo '<em>false</em>';
                    elseif($v===null) echo '<em>null</em>';
                    else echo print_r($v, true);
                ?></td>
            </tr>
        <?php
        }
        
        ?>
        </table>
    </div>
    <div class="span6">
        <h2>Environment</h2>
        <table class="table table-striped table-condensed">
            <tr>
                <th>current module</th>
                <td><?php echo App::module()->name(); ?></td>
            </tr>
            <tr>
                <th>loaded modules</th>
                <td><?php echo implode(", ", array_keys(Ac::loader()->getModules())); ?></td>
            </tr>
        <?php
        $vars = Ac::router()->toArray();
        foreach($vars as $i => $v){
            if($i == "controllerInstance") continue;
            if($i == "resource") $v='['.implode(",", $v).']';
            if($i == "params") $v='['.implode(",", $v).']';
            ?>
            <tr>
                <th><?php echo $i; ?></th>
                <td><?php 
                    if($v===true) echo '<em>true</em>';
                    elseif($v===false) echo '<em>false</em>';
                    elseif($v===null) echo '<em>null</em>';
                    else echo print_r($v, true);
                ?></td>
            </tr>
        <?php
        }
        
        ?>
        </table>
        <h2>Variables</h2>
        <table class="table table-striped table-condensed">
            <tr>
                <th>GET</th>
                <td><?php echo print_r($_GET, true); ?></td>
            </tr>
            <tr>
                <th>POST</th>
                <td><?php echo print_r($_POST, true); ?></td>
            </tr>
            <tr>
                <th>INPUT</th>
                <td><?php echo print_r($GLOBALS["_INPUT"], true); ?></td>
            </tr>
            <tr>
                <th>SESSION</th>
                <td><?php echo print_r($_SESSION, true); ?></td>
            </tr>
            <tr>
                <th>COOKIE</th>
                <td><?php echo print_r($_COOKIE, true); ?></td>
            </tr>
        </table>
    </div>
</div>
<style>
    .ac-debug table,
    .ac-debug table tbody,
    .ac-debug table tr{
        max-width: 460px;
        width: 460px;
    }
    .ac-debug table td{
        max-width: 160px;
    }
</style>