<?php
include 'Tags/TagsManager.class.php';
include 'Tags/TemplateStrategies/Strategy.class.php';
include 'Tags/TemplateStrategies/DefaultStrategy.class.php';
include 'Tags/TemplateStrategies/RenderStrategy.class.php';
include 'Tags/TagStrategies/Strategy.class.php';
include 'Tags/TagStrategies/DefaultStrategy.class.php';
include 'Tags/TagStrategies/DoctypeStrategy.class.php';
include 'Tags/TagStrategies/ScriptStrategy.class.php';
include 'Parser.class.php';
include 'CodeNode.class.php';
include 'View.class.php';
include 'ViewParser.class.php';

$startTime = microtime(true);

for ($i = 0; $i < 1; $i++) {
  $View = View::fromFile('Views/part1.php');
  var_dump($View->render(array(), true));
}

// $dom = new DomDocument();

// $dom->loadHTMLFile ('_Cache/part1.php');

// var_dump($dom->saveHTML());
echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>