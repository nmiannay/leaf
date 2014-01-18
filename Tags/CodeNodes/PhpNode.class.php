<?php
namespace Tags\CodeNodes;
/**
*
*/
class PhpNode extends \DOMProcessingInstruction
{
  public function __construct($value = null)
  {
    parent::__construct('php', $value.' ');
  }


  public function appendChild(\DOMNode $newChild)
  {
    if ($newChild instanceof PhpNode){
      $code = array();

      if ($this->data[0] != "\n") {
        $code[] = "";
        $this->data = substr($this->data, 0, -2).'{';
      }
      $code[] = rtrim($this->data, "}\n");
      $code[] = rtrim($newChild->data, ' ');
      $code[] = "}";

      $this->data = implode("\n", $code);
    }
    else
      $this->parentNode->appendChild($newChild);
  }
}