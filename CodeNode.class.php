<?php
/**
*
*/
class CodeNode extends \DOMProcessingInstruction
{
  public $closingTag = null;
  private $needend   = false;
  private $matches   = array();

  public function __construct($name, $value = null)
  {
    parent::__construct($name, $value);
    $this->needend = preg_match('/(if|foreach|for|while)\s*(?:\((.*)\)|(.*))$/', $value, $this->matches) >= 1;
  }

  public function appendChild(\DOMNode $newChild)
  {
    if ($this->needend && $this->parentNode != null) {
      $this->data       = sprintf('%s (%s): ', $this->matches[1], $this->matches[2] ?: $this->matches[3]);
      $this->closingTag = new \DOMProcessingInstruction ('php', sprintf('end%s; ', $this->matches[1]));

      if ($this !== $this->parentNode->lastChild) {
        $this->parentNode->insertBefore($this->closingTag, $this->parentNode->lastChild);
      }
      else {
        $this->parentNode->appendChild($this->closingTag);
      }
      $this->needend = false;
    }
    $this->parentNode->insertBefore($newChild, $this->closingTag);
    parent::appendChild($newChild);
  }
}