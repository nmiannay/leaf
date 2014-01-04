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
include 'ViewParser.class.php';
include 'ViewStream.class.php';

$startTime = microtime(true);
$items     = array('game' => 10);
stream_wrapper_register('phs', 'ViewStream');


include 'phs://Views/part1.php';

echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';
?>