<?php
/**
*
*/
use Tags\TagsManager;
use Tags\TagStrategies;
use Tags\TemplateStrategies;
class ViewStream extends \DOMImplementation
{
  private $cachefile;
  private $Dom;
  private $TagsManager;

  public $mode;
  public $url;
  public $filename;
  public $options;
  public $_eof = false;

  private static $_isRegistered = false;
  const TPL_NS = 'http://xyz';
  const SCHEME = 'phs';

  public function __construct()
  {
    $this->Dom                     = $this->createDocument(null, null);
    $this->Dom->preserveWhiteSpace = false;
    $this->Dom->formatOutput       = true;
    $this->TagsManager             = new TagsManager($this->Dom);

    $this->TagsManager->registerStrategy('doctype', new TagStrategies\DoctypeStrategy());
    $this->TagsManager->registerStrategy('script', new TagStrategies\ScriptStrategy());
    $this->TagsManager->registerTempalateStrategy('render', new TemplateStrategies\RenderStrategy());
  }

  public function getDom() { return ($this->Dom); }
  public function getTagsManager() { return ($this->TagsManager); }

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
    return ($this->Dom);
  }

  public function unwrap(\DOMNode $OldNode) {
    while($OldNode->hasChildNodes()) {
      $OldNode->parentNode->insertBefore($OldNode->firstChild, $OldNode);
    }
    return ($OldNode->parentNode->removeChild($OldNode));
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    $this->url      = parse_url($path);
    $this->filename = $this->url['host'].$this->url['path'];
    $this->mode     = $mode;
    $this->options  = $options;
    $extended_file  = null;

    for ($i = 0; $i < 2; $i++)
    {
      if ($this->Dom !== null && $extended_file !== null) {
        $ChildView = new ViewStream();
        $ChildView->stream_open(sprintf('%s://%s', $this->url['host'], $extended_file), $mode, $options, $opened_path);
        $this->Dom = $ChildView->mergeWith($this->Dom);
      }
      else {
        $Parser = new ViewParser($this);
      }
      if (($ExtendsNode = $this->Dom->getElementsByTagName('extends')->item(0)) !== null) {
        $this->Dom->removeChild($ExtendsNode);
        $extended_file = $ExtendsNode->getAttribute("value");
      }
      else {
        break;
      }
    }
    return (true);
  }

  public function stream_read($count)
  {
      if ($this->_eof || !$count) {
        return '';
      }
      $this->_eof = true;
      foreach ($this->Dom->getElementsByTagNameNS(ViewStream::TPL_NS, '*') as $TplNode) {
        $this->unwrap($TplNode);
      }
      return ($this->Dom->saveXML());
  }

  public function stream_eof()
  {
      return ($this->_eof);
  }

  public function stream_stat()
  {
    return (stat($this->filename));
  }

  public function __destruct()
  {
    unset($this->View);
  }
}
?>