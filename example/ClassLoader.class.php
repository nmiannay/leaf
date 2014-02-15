<?php
class ClassLoader
{
  protected static $namespaces = array();
  protected static $prefixes   = array();

  public static  function getNamespaces()
  {
    return (self::$namespaces);
  }
  public static  function getPrefixes()
  {
    return (self::$prefixes);
  }
  public static  function registerNamespaces(array $namespaces)
  {
    foreach ($namespaces as $namespace => $path) {
      self::$namespaces[$namespace] = (array)$path;
    }
  }
  public static  function registerNamespace($namespace, $path)
  {
    self::$namespaces[$namespace] = (array)$path;
  }
  public static function register()
  {
    spl_autoload_register(array('library\ClassLoader', 'loadClass'));
  }

  public static function loadClass($class)
  {
    if (false !== ($pos = strripos($class, '\\'))) {
      $namespace = substr($class, 0, $pos);

      foreach (self::$namespaces as $ns => $dirs) {
        if (0 === strpos($namespace, $ns)) {
          foreach ($dirs as $dir) {
            $file = str_replace('\\', __DS__, $dir . __DS__ . $namespace) . __DS__ . substr($class, $pos + 1) . '.class.php';

            if (file_exists($file)) {
              require $file;
              if (method_exists($class, '__load')) {
                $class::__load();
              }
            }
            return;
          }
        }
      }
    }
    else {
      $file = __DIR__ . __DS__ . $class . '.class.php';

      if (file_exists($file)) {
        require $file;
        if (method_exists($class, '__load')){
          $class::__load();
        }
      }
    }
  }
}
