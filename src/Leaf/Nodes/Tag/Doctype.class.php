<?php
namespace Leaf\Nodes\Tag;

class Doctype extends Common
{
  protected $doctype;

  public function __construct($tagName, $textContent = null)
  {
    $this->doctype = 'html';
    parent::__construct('doctype');
  }

  public static function render(\Leaf\Node $Node)
  {
    return (sprintf("<!DOCTYPE %s>", $Node->nodeValue));
  }
}