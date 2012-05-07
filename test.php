<?php

//THIS IS ONLY A TEST

$ctr = new Controller_Index("main");
$ctr->__default();
$ctr->context()->response->send();
?>
<pre>
<?php
    echo htmlspecialchars(print_r($this, true));
?>
</pre>