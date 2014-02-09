<?php
/**
*
*/
class Test
{
  const FILEMANE = '.php_test';

  public function assertEqual($reality, $expect)
  {
    if ($reality !== $expect) {
      throw new Exception("Assert fail, expect: <pre>".htmlentities($expect)."</pre> but get: <pre>".htmlentities($reality)."</pre>", 1);
    }
  }

  public function evalLeaf($leaf)
  {
    file_put_contents(Test::FILEMANE, $leaf);
    $html = file_get_contents('leaf://'.Test::FILEMANE.'?cache=false');

    return (preg_replace('/[\n\r]/', '', $html));
  }

  public static function runtests($dirname)
  {
    $total = 0;
    $pass = 0;
    $html = array();

    if (!file_exists(sprintf('%s/%s.class.php', $dirname, $dirname))) {
      throw new \Exception(sprintf('Cannot run test %s: class file is missing', $dirname), 1);
    }
    include sprintf('%s/%s.class.php', $dirname, $dirname);

    $class_name = ucfirst($dirname);
    $html[] = "Run tests $dirname :";
    foreach (get_class_methods($class_name) as $method) {
      if (preg_match('/^test_/', $method)) {
        $html[] = sprintf("Test %s :\n", str_replace('_', ' ', substr($method, 5)));
        try {
          $TestClass = new $class_name();
          $TestClass->$method();
          $pass++;
          $html[] = "OK";
        } catch (Exception $e) {
          $html[] = "KO, ".$e->getMessage();
        }
        $total++;
      }
    }
    $html[] = sprintf("Result: %d Tests, %d Success, %d Errors", $total, $pass, $total - $pass);
    echo implode("<br>", $html);
    unlink(self::FILEMANE);
  }
}