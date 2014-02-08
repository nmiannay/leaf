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

  public function __toHtml()
  {
    return (sprintf("<!DOCTYPE $this->doctype>"));
  }
}