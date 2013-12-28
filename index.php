<?php
include 'Application.class.php';
include 'Parser.class.php';
include 'CodeNode.class.php';
include 'BlockNode.class.php';
include 'View.class.php';
include 'ViewParser.class.php';

$startTime = microtime(true);

for ($i=0; $i < 1; $i++) {
  $View = View::fromFile('Views/part1.php');
  // echo ($View->render());
}

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';//18s pour 100 boucles
?>