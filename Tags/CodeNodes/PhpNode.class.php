<?php
namespace Tags\CodeNodes;
/**
*
*/
class PhpNode extends \DOMProcessingInstruction
{
  protected $closingTag = null;
  public function __construct($value, $closure = null)
  {
    parent::__construct('php', $value);
    if ($closure !== null) {
      $this->closingTag = new \DOMProcessingInstruction ('php', $closure.';');
    }
  }

  public function appendChild(\DOMNode $newChild)
  {
    if ($this->closingTag === null) {
      $this->closingTag = $this->nextSibling;
    }
    elseif ($this->closingTag->ownerDocument === null) {
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