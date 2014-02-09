<?php
namespace Leaf;

use Leaf\TagsManager;
use Leaf\TagStrategies;
use Leaf\TemplateStrategies;
use Leaf\CodeStrategies;
use Leaf\TagModifiers;
class Stream
{
  private $Document;
  private $mode;
  private $url;
  private $opened_path;
  private $options = array();
  private $eof     = false;

  private $TagsManager;
  private $Error = null;

  const NS            = 'leaf';
  const SCHEME        = 'leaf';
  const CACHEDIR      = '._Cache/';
  const DIR_SEPARATOR = '_';

  public function __construct()
  {
    $this->Document = new Document();
    $this->TagsManager = new Nodes\Manager($this->Document);
  }

  public function getDom() { return ($this->Document); }
  public function getFilename() { return ($this->opened_path ?: $this->url['host'].(isset($this->url['path']) ? $this->url['path'] : '')); }
  public function getCachename() { return (self::CACHEDIR.str_replace('/', self::DIR_SEPARATOR, $this->getFilename())); }
  public function getTagsManager() { return ($this->TagsManager); }

  public static function init()
  {
    stream_wrapper_register(self::SCHEME, 'Leaf\\Stream') or die("Failed to register protocol");
  }
  public function cache_is_active()
  {
    return (!isset($this->options['cache']) || ((bool) $this->options['cache'] && $this->options['cache'] !== 'false'));
  }
  private function mergeWith(&$Parent)
  {
    $blocks       = $Parent->getDom()->getElementsByTagNameNS('leaf', 'block');
    $child_blocks = $this->Document->getElementsByTagNameNS('leaf', 'block');

    foreach ($blocks as $ParentBlock) {
      $blockId = $ParentBlock->getAttribute("value");

      for ($i = $blocks->length - 1; $i >= 0; $i--) {
        $ChildBlock = $blocks->item($i);

         if ($ChildBlock->getAttribute("value") == $blockId) {
          $OldNode   = $ParentBlock->parentNode->replaceChild($ChildBlock, $ParentBlock);
          $tplParent = $OldNode->getElementsByTagNameNS('leaf', 'parent')->item(0);

          if ($tplParent !== null) {
            $tplParent->parentNode->replaceChild($tplParent, $ChildBlock);
          }
        }
      }
    }
    return ($Parent->Document);
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    $this->url         = parse_url($path);
    $this->opened_path = $opened_path;
    $this->mode        = $mode;
    $extended_file     = null;

    try {
      if (isset($this->url['query'])) {
        parse_str($this->url['query'], $output);
        $this->options += $output;
      }
      if ($this->need_to_rebuild()) {
        $Parser         = new LeafParser($this);
        $template_nodes = $this->Document->getElementsByTagNameNS('leaf', 'extends');
        $extends        = $template_nodes->item(0);

        if ($extends !== null && $extends->getAttribute('value')) {
          $val      = $extends->getAttribute('value');
          $new_file = $val[0] == '/' ? $val : ($this->url['host'] . dirname($this->url['path']) . DIRECTORY_SEPARATOR . $val);
          $Parent   = new Stream();

          $Parent->stream_open(sprintf('%s://%s', $this->url['scheme'], $new_file), $mode, $options, $opened_path);
          $this->Document = $this->mergeWith($Parent);
        }
      }
      return (true);
    }
    catch (\Exception $E) {
      $this->Error = new \Exception($E->getMessage(), $E->getCode());
      return (false);
    }
  }

  public function stream_read($count)
  {
    if (!$this->eof || !$count) {
      $this->eof = true;
      if (!$this->need_to_rebuild()) {
        return (file_get_contents($this->getCachename()));
      }
      else {
        return ($this->Document->__toHtml($this));
      }
    }
    return ('');
  }

  public function stream_eof()
  {
      return ($this->eof);
  }

  public function stream_stat()
  {
    if (file_exists($this->getFilename())) {
      return (stat($this->getFilename()));
    }
  }

  public function need_to_rebuild()
  {
    return (!file_exists($this->getCachename()) || filemtime($this->getFilename()) > filemtime($this->getCachename()));
  }

  public function stream_flush()
  {
    if ($this->cache_is_active() && $this->need_to_rebuild()) {
      if (!file_exists(self::CACHEDIR)) {
        mkdir(self::CACHEDIR);
      }
      touch($this->getCachename(), filemtime($this->getFilename()));
      file_put_contents($this->getCachename(), $this->Document->__toHtml());
    }
  }
  public function __destruct()
  {
    if ($this->Error !== null) {
      throw $this->Error;
    }
  }
}
?>