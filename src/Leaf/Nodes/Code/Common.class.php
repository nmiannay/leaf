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
    $html[] = trim($Node->firstChild->textContent, ';').'; ';

    for ($i = 1; $i < $Node->childNodes->length; $i++) {
      $html[] = $Node->childNodes->item($i)->__toHtml();
    }
    $html[] = '?>';
    return (implode('', $html));
  }
}