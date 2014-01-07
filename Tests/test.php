<?php
require '../autoload.php';

stream_wrapper_register(ViewStream::SCHEME, 'ViewStream') or die("Failed to register protocol");

$test_files = scandir(__DIR__);
$startTime  = microtime(true);

$items     = array('game' => 10);
foreach ($test_files as $dirname) {
  if ($dirname[0] != '.' && is_dir($dirname)) {
    runtest($dirname);
  }
}
echo '<br/>'.number_format(microtime(true) - $startTime, 4), 's';




function runtest($dirname)
{
  if (!file_exists($dirname.'/input.php')) {
    throw new \Exception(sprintf('Cannot run test %s: input file is missing', $dirname), 1);
  }
  if (!file_exists($dirname.'/output.php')) {
    throw new \Exception(sprintf('Cannot run test %s: output file is missing', $dirname), 1);
  }

  printf('Strat test %s :<br/>', $dirname);
  $input = file_get_contents('leaf://'.$dirname.'/input.php?cache=false');
  $output = file_get_contents($dirname.'/output.php');

  if ($input === $output) {
    var_dump('OK', $input, $output);
  }
  else {
    var_dump('KO', $input, $output);
  }
}
?>