<?php
namespace Leaf;
/**
*
*/
class Document extends \DomDocument
{
  public function saveHTML()
  {
    $html = array();
    foreach ($this->childNodes as $Child) {
      // Document::toHTML($Child);
      var_dump(get_class($Child));
      $html[] = $Child->__toHTML();
    }
    return (implode('', $html));
  }

  public static function toHTML($Node)
  {

    $Dom = new \DomDocument();
    $tt = $Dom->importNode($Node);
    $Dom->appendChild($tt);
    var_dump($Dom->saveHTML());
  }
}
?>