<?php
namespace Leaf\Nodes\Tag;


class Common extends \Leaf\Node
{
  private static $self_closing = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'base');

  public function  __construct($tagName, $textContent = null)
  {
    parent::__construct('LeafTag:' . $tagName, $textContent, 'Leaf');
  }



  public static function render(\Leaf\Node $Node)
  {
    $html = array();

    if (in_array($Node->localName, self::$self_closing)) {
      return (sprintf("<$Node->localName%s />", $Node->getAttributes_str()));
    }
    else {
      $html[] = sprintf("<$Node->localName%s>", $Node->getAttributes_str());
      foreach ($Node->childNodes as $Child) {
        $html[] = $Child->__toHtml();
      }
      $html[] = "</$Node->localName>";
    }
    return (implode('', $html));
  }
}
?>