<?php
include 'Tags/Tags.class.php';
include 'Tags/Strategies/Strategy.class.php';
include 'Tags/Strategies/DoctypeStrategy.class.php';
include 'Tags/Strategies/ScriptStrategy.class.php';
include 'Parser.class.php';
include 'CodeNode.class.php';
include 'BlockNode.class.php';
include 'View.class.php';
include 'ViewParser.class.php';

$startTime = microtime(true);

for ($i = 0; $i < 1; $i++) {
  $View = View::fromFile('Views/part1.php');
  var_dump($View->render(array(), true));
}

$dom = new DomDocument();

$dom->loadHTMLFile ('_Cache/part1.php');

var_dump($dom->saveHTML());
echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>