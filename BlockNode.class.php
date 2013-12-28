<?php
/**
*
*/
class BlockNode extends \DOMDocumentFragment
{
  private $Marker;
  private $value;
  private $type;

  public function createMarker($type, $value)
  {
    $this->Marker = new \DOMElement('tpl:'.$type, null, 'http://xyz');
    $this->appendChild($this->Marker);
    $this->Marker->setAttribute('value', trim($value, '"'));
    $this->type   = $type;
    $this->value  = $value;
  }
  public function appendChild(\DOMNode $newChild)
  {
    if ($this->Marker !== null && $this->Marker !== $newChild) {
      $this->Marker->appendChild($newChild);
    }
    else {
      parent::appendChild($newChild);
    }
  }
}