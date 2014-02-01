<?php
require 'autoload.php';
$startTime = microtime(true);
$items     = array('game' => 10);
$year      = date('Y');
$author    = 'Miannay Nicolas';
stream_wrapper_register(Leaf\ViewStream::SCHEME, 'Leaf\\ViewStream') or die("Failed to register protocol");

// include 'leaf://./Views/part1.php.leaf?cache=false';
include 'leaf://Views/part1.php.leaf?cache=true';

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>