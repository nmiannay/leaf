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
    self::$namespaces += $namespaces;
  }
  public static  function registerNamespace($namespace, $path)
  {
    self::$namespaces[$namespace] = $path;
  }
  public static function register()
  {
    spl_autoload_register(array('ClassLoader', 'loadClass'));
  }

  public static function loadClass($class)
  {
    if (false !== ($pos = strripos($class, '\\'))) {
      $namespace = substr($class, 0, $pos);

      foreach (self::$namespaces as $ns => $dir) {
        if (0 === strpos($namespace, $ns)) {
          $class = substr($class, $pos + 1);
          $file  = $dir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.class.php';
          if (file_exists($file)) {
            require $file;
          }
          return;
        }
      }
    }
    else
    {
      $file = __DIR__.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.class.php';
      if (file_exists($file)) {
        require $file;
      }
    }
  }
}