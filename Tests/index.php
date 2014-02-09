<?php
require '../example/autoload.php';
require 'test.class.php';

\Leaf\Stream::register();

$test_files = scandir(__DIR__);
$startTime  = microtime(true);

foreach ($test_files as $dirname) {
  if ($dirname[0] != '.' && is_dir($dirname)) {
    Test::runtests($dirname);
  }
}
echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';

?>