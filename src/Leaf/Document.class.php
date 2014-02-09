<?php
namespace Leaf;
/**
*
*/
class Document extends \DomDocument
{
  private $Manager;

  public function setManager(Nodes\Manager $Manager)
  {
    $this->Manager = $Manager;
  }
  public function getManager()
  {
    return ($this->Manager);
  }
  public function __toHtml()
  {
    $html = array();
    foreach ($this->childNodes as $Child) {
      $html[] = $Child->__toHTML();
    }
    return (implode('', $html));
  }
}
?>