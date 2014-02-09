<?php
namespace Leaf\Nodes\Code;
/**
*
*/
class Common extends \Leaf\Node
{
  public function __construct($code)
  {
    parent::__construct('LeafCode:common', $code, 'leaf');
  }

  public static function render(\Leaf\Node $Node)
  {
    $html   = array('<?php ');
    $html[] = trim($Node->nodeValue, ';').';';
    foreach ($Node->childNodes as $Child) {
      $html[] = $Child->__toHtml();
    }
    $html[] = '?>';
    return (implode('', $html));
  }
}