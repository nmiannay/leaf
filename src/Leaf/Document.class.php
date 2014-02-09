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
      // Document::toHTML($Child);
      // var_dump([get_class($Child), $Child->tagName]);
      $html[] = $Child->__toHTML();
    }
    return (implode('', $html));
  }

  public static function prevHTML($Node)
  {

    $Dom = new \DomDocument();
    $tt = $Dom->importNode($Node);
    $Dom->appendChild($tt);
    // var_dump($Dom->saveHTML());
  }
}
?>