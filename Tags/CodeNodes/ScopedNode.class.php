<?php
namespace Tags\CodeNodes;
/**
*
*/
class ScopedNode extends PhpNode
{
  protected $closingTag = null;

  public function __construct($value, $closure = null)
  {
    parent::__construct($value);
    if ($closure !== null) {
      $this->closingTag = new \DOMProcessingInstruction ('php', $closure.'; ');
    }
  }

  public function appendChild(\DOMNode $newChild)
  {
    if ($this->closingTag !== null && $this->closingTag->ownerDocument === null) {
      if ($this !== $this->parentNode->lastChild) {
        $this->parentNode->insertBefore($this->closingTag, $this->parentNode->lastChild);
      }
      else {
        $this->parentNode->appendChild($this->closingTag);
      }
    }
    $this->parentNode->insertBefore($newChild, $this->closingTag);
  }
}