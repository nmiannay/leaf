<?php
/**
* @class View
*/
use Tags\Tag;
use Tags\Strategies;
class View extends \DOMImplementation
{
  private $cachefile;
  private $vars;
  private $Dom;

  protected static $cache_dir = './_Cache/';

  private static $moustache_regexp;
  const TPL_NS = 'http://xyz';
  /**
  * @param $path string Path to the template.
  * @param $vars array Variables that will be passed to the view.
  */
  public function __construct(array $vars = array())
  {
    $this->Dom                     = $this->createDocument(null, null, \DOMImplementation::createDocumentType("html"));
    $this->Dom->preserveWhiteSpace = false;
    $this->Dom->formatOutput       = true;
    $this->vars                    = $vars;

    $this->Dom->registerNodeClass('DOMElement', 'Tags\\Tag');
    Tag::registerStrategy('doctype', new Strategies\DoctypeStrategy());
    Tag::registerStrategy('script', new Strategies\ScriptStrategy());
  }

  public function getDom()
  {
    return ($this->Dom);
  }

  public static function fromFile($filename, array $vars = array())
  {
    $View = null;
    for ($i = 0; $i < 2; $i++)
    {
      if ($View !== null && $extended_file !== null) {
        $View = ViewParser::parseFile($extended_file)->mergeWith($View->getDom());
      }
      else {
        $View = ViewParser::parseFile($filename);
      }

      if (($ExtendsNode = $View->getDom()->getElementsByTagName('extends')->item(0)) !== null) {
        $View->getDom()->removeChild($ExtendsNode);
        $extended_file = $ExtendsNode->getAttribute("value");
      }
      else {
        break;
      }
    }
    $View->cachefile = basename($filename);
    return ($View);
  }

/** GETTER */
    public static function getCacheDir(){ return (self::$cache_dir); }

  /**
  * @brief Merges two views tree for the inheritance.
  * @param $child array The child view's tree.
  */
  private function mergeWith(\DomDocument $Child)
  {
    $ParentBlocks = $this->Dom->getElementsByTagName('block');

    foreach ($Child->getElementsByTagName('block') as $ChildBlock) {
      $blockId = $ChildBlock->getAttribute("value");

      for ($i = $ParentBlocks->length -1; $i >= 0; $i--) {
        $ParentBlock = $ParentBlocks->item($i);

        if ($ParentBlock->getAttribute("value") == $blockId) {
          $import = $this->Dom->importNode($ChildBlock, true);
          $OldParent = $ParentBlock->parentNode->replaceChild($import, $ParentBlock);

          if (($tplParent = $import->getElementsByTagName('parent')->item(0)) !== null) {
            $tplParent->parentNode->replaceChild($OldParent, $tplParent);
            $this->unwrap($OldParent);
          }
        }
      }
    }
    return ($this);
  }

  public function unwrap(\DOMNode $OldNode) {
    while($OldNode->hasChildNodes()) {
      $OldNode->parentNode->insertBefore($OldNode->firstChild, $OldNode);
    }
    return ($OldNode->parentNode->removeChild($OldNode));
  }

  /**
  * @brief Render the template view.
  * @param $vars array Variables that will be passed to the view.
  */
  public function render(array $vars = array(), $rerender = false)
  {
    if ($rerender || !file_exists(self::$cache_dir.$this->cachefile)) {
      foreach ($this->Dom->getElementsByTagNameNS(View::TPL_NS, '*') as $TplNode) {
        $this->unwrap($TplNode);
      }
      if (!file_exists('_Cache')) {
        mkdir(self::$cache_dir);
      }
      file_put_contents(self::$cache_dir.$this->cachefile, $this->Dom->saveXML());
    }
    $this->vars += $vars;
    $render_sandbox = function(){
      ob_start();
        extract($this->vars);
          require self::$cache_dir.$this->cachefile;
      return (ob_get_clean());
    };

    $render_sandbox->bindTo($this);
    return ($render_sandbox());
  }
}
?>