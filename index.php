<?php
require 'autoload.php';

$startTime = microtime(true);
$items     = array('game' => 10);
stream_wrapper_register('phs', 'ViewStream');


include 'phs://Views/part1.php';

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>