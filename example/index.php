<?php
require 'autoload.php';
$startTime = microtime(true);
$items     = array('game' => 10);
$year      = date('Y');
$author    = 'Miannay Nicolas';

Leaf\Stream::register();

include 'leaf://./views/part1.php.leaf?cache=false';

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>