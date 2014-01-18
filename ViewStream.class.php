<?php
/**
*
*/
use Tags\TagsManager;
use Tags\TagStrategies;
use Tags\TemplateStrategies;
use Tags\CodeStrategies;
class ViewStream extends \DOMImplementation
{
  private $Dom;
  private $TagsManager;
  private $mode;
  private $url;
  private $opened_path;
  private $options = array();
  private $eof     = false;

  const TPL_NS        = 'http://xyz';
  const PHP_NS        = 'http://php.net';
  const SCHEME        = 'leaf';
  const CACHEDIR      = '._Cache/';
  const DIR_SEPARATOR = '_';

  public function __construct()
  {
    // var_dump(__FUNCTION__);
    $this->Dom                     = $this->createDocument(null, null);
    $this->Dom->encoding           = 'UTF-8';
    $this->Dom->preserveWhiteSpace = false;
    $this->Dom->formatOutput       = true;
    $this->Dom->substituteEntities = true;
    $this->TagsManager             = new TagsManager($this->Dom);

    $this->Dom->registerNodeClass('DOMProcessingInstruction', 'Tags\CodeNodes\PhpNode');
    $this->TagsManager->registerStrategy('doctype', new TagStrategies\DoctypeStrategy());
    $this->TagsManager->registerTempalateStrategy('render', new TemplateStrategies\RenderStrategy());
    $this->TagsManager->registerCodeStrategy('foreach', new CodeStrategies\LoopStrategy());
    $this->TagsManager->registerCodeStrategy('for', new CodeStrategies\LoopStrategy());
    $this->TagsManager->registerCodeStrategy('while', new CodeStrategies\LoopStrategy());
    $this->TagsManager->registerCodeStrategy('if', new CodeStrategies\ConditionalStrategy());
    $this->TagsManager->registerCodeStrategy('else', new CodeStrategies\ConditionalStrategy());
  }

  public function getDom() { return ($this->Dom); }
  public function getFilename() { return ($this->opened_path ?: $this->url['host'].(isset($this->url['path']) ? $this->url['path'] : '')); }
  public function getCachename() { return (self::CACHEDIR.str_replace('/', self::DIR_SEPARATOR, $this->getFilename())); }
  public function getTagsManager() { return ($this->TagsManager); }


  public function cache_is_active()
  {
    return (isset($this->options['cache']) && (bool) $this->options['cache'] && $this->options['cache'] !== 'false');
  }
  private function mergeWith(\DomDocument $Child)
  {
    $ParentBlocks = $this->Dom->getElementsByTagName('block');

    foreach ($Child->getElementsByTagName('block') as $ChildBlock) {
      $blockId = $ChildBlock->getAttribute("value");

      for ($i = $ParentBlocks->length -1; $i >= 0; $i--) {
        $ParentBlock = $ParentBlocks->item($i);

        if ($ParentBlock->getAttribute("value") == $blockId) {
          $import    = $this->Dom->importNode($ChildBlock, true);
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
    while ($OldNode->hasChildNodes()) {
      $OldNode->parentNode->insertBefore($OldNode->firstChild, $OldNode);
    }

    return ($OldNode->parentNode->removeChild($OldNode));
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    // var_dump(__FUNCTION__);
    $this->url         = parse_url($path);
    $this->opened_path = $opened_path;
    $this->mode        = $mode;
    $this->options     = $options ?: [];
    $extended_file     = null;

    if (isset($this->url['query'])) {
      parse_str($this->url['query'], $output);
      $this->options += $output;
    }

    try {
      if ($this->need_to_rebuild()) {
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
          if (($ExtendsNode = $this->Dom->getElementsByTagName('extends')->item(0)) === null) {
            break;
          }
          $this->Dom->removeChild($ExtendsNode);
          $extended_file = $ExtendsNode->getAttribute("value");
        }
      }

      return (true);
    }
    catch (\Exception $E) {
      throw new \Exception($E->getMessage(), $E->getCode());
      return (false);
    }
  }

  public function stream_read($count)
  {
    // var_dump(__FUNCTION__);
    if (!$this->eof || !$count) {
      $this->eof = true;
      // if (!$this->need_to_rebuild()) {
      //   return (file_get_contents($this->getCachename()));
      // }
      // else {
        foreach ($this->Dom->getElementsByTagNameNS(ViewStream::TPL_NS, '*') as $TplNode) {
          $this->unwrap($TplNode);
        }
        return ($this->Dom->saveXML());
      // }
    }

    return ('');
  }

  public function stream_eof()
  {
    // var_dump(__FUNCTION__);
      return ($this->eof);
  }

  public function stream_stat()
  {
    // var_dump(__FUNCTION__);
    if (file_exists($this->getFilename())) {
      return (stat($this->getFilename()));
    }
  }

  public function __destruct()
  {
    // var_dump(__FUNCTION__);
    unset($this->Dom);
  }

  public function need_to_rebuild()
  {
    // var_dump(__FUNCTION__);
    return (true);
    return (!file_exists($this->getCachename()) || filemtime($this->getFilename()) > filemtime($this->getCachename()));
  }

  public function stream_flush()
  {
    // var_dump(__FUNCTION__);
    // if ($this->cache_is_active() && $this->need_to_rebuild()) {
      if (!file_exists(self::CACHEDIR)) {
        mkdir(self::CACHEDIR);
      }
      touch($this->getCachename(), filemtime($this->getFilename()));
      file_put_contents($this->getCachename(), $this->Dom->saveXML());
      // exit();
    }
  // }
}
?>