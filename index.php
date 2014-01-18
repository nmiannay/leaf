<?php
require 'autoload.php';
$startTime = microtime(true);
$items     = array('game' => 10);
stream_wrapper_register(ViewStream::SCHEME, 'ViewStream') or die("Failed to register protocol");


include 'leaf://Views/part1.php.leaf?cache=false';

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>