<?php
/**
* @class View
*/
class View extends \DOMDocument
{
  private $cachefile;
  private $vars;

  protected static $cache_dir = '_Cache';

  private static $moustache_regexp;

  /**
  * @param $path string Path to the template.
  * @param $vars array Variables that will be passed to the view.
  */
  public function __construct(array $vars = array())
  {
    parent::__construct('5', 'UTF-8');
    $this->preserveWhiteSpace = false;
    $this->formatOutput       = true;
    $this->vars               = $vars;
    $this->cachefile          = self::$cache_dir.str_replace('\\', '_', '_Cachepart1.php');
    // $this->cachefile          = self::$cache_dir.str_replace('\\', '_', $this->path);
  }

  public static function fromFile($filename, array $vars = array())
  {
    for ($i = 0, $parentPath = null; $i < 2; $i++)
    {
      if ($parentPath !== null) {
        $realpath  = self::parsePath($parentPath);
        $View = ViewParser::parseFile($parentPath)->mergeWith($View);
      }
      else {
        $realpath  = self::parsePath($filename);
        $View = ViewParser::parseFile($realpath);
      }

      if (($ExtendsNode = $View->getElementsByTagName('extends')->item(0)) !== null) {
        $View->removeChild($ExtendsNode);
        $parentPath = $ExtendsNode->getAttribute("value");
      }
      else {
        break;
      }
    }
    return ($View);
  }

/** GETTER */
    public static function getCacheDir(){ return (self::$cache_dir); }

  /**
  * @brief Merges two views tree for the inheritance.
  * @param $child array The child view's tree.
  */
  private function mergeWith(View $Child)
  {
    $ParentBlocks = $this->getElementsByTagName('block');

    foreach ($Child->getElementsByTagName('block') as $ChildBlock) {
      $blockId = $ChildBlock->getAttribute("value");

      for ($i = $ParentBlocks->length -1; $i >= 0; $i--) {
        $ParentBlock = $ParentBlocks->item($i);

        if ($ParentBlock->getAttribute("value") == $blockId) {
          $import = $this->importNode($ChildBlock, true);
          $OldParent = $ParentBlock->parentNode->replaceChild($import, $ParentBlock);

          if (($tplParent = $import->getElementsByTagName('parent')->item(0)) !== null) {
            $this->replaceWithChildren($OldParent, $tplParent);
          }
        }
      }
    }
    return ($this);
  }

  private function replaceWithChildren(\DOMNode $NewNode, \DOMNode $OldNode)
  {
    $Frag = $this->createDocumentFragment();

    foreach ($NewNode->childNodes as $ChildNode) {
      $Frag->appendChild($ChildNode);
    }
    $OldNode->parentNode->replaceChild($Frag, $OldNode);
    return ($OldNode);
  }

  /**
  * @brief Render the template view.
  * @param $vars array Variables that will be passed to the view.
  */
  public function render(array $vars = array())
  {
    if (Application::in_devMode() || !file_exists($this->cachefile)) {
      // file_put_contents($this->cachefile, $this->saveHTML());
      file_put_contents($this->cachefile, $this->saveXML());
    }
    $this->vars += $vars;
    $render_sandbox = function(){
      ob_start();
        extract($this->vars);
          require $this->cachefile;
      return (ob_get_clean());
    };

    $render_sandbox->bindTo($this);
    return ($render_sandbox());
  }

  /**
  * @brief Parse the path to get the real absolute path to the template file
  * @param $toParse string Path to the view.
  * @param $sub_dir string The subdirectory's path of the View's file.
  */
  static public function parsePath($toParse, $sub_dir = 'Modules/%s/Ressources/Views/%s')
  {
    $file_name   = substr(strstr($toParse, '@'), 1);
    $module_path = strstr($toParse, '@', true);
    $full_name   = ($module_path == '') ? sprintf(substr(strstr($sub_dir, '%s'), 2), $file_name) : sprintf($sub_dir, $module_path, $file_name);

    switch (true)
    {
      case ($realpath = realpath(Application::getAppDir().$toParse)) === false:
        throw new \Exception("View `$full_name' doesn't exist", 1);
        break;
      case is_dir($realpath):
        throw new \Exception("Can't open `$full_name' because it's a directory, it must be a file", 1);
        break;
      case !is_readable($realpath):
        throw new \Exception("Can't open `$full_name'", 1);
        break;
      default:
        return ($realpath);
        break;
    }
  }
}
?>