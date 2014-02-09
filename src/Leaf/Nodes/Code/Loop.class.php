<?php
namespace Leaf\Nodes\Code;
/**
*
*/
class Loop extends \Leaf\Node
{
  public $type;

  public function __construct($type, $code)
  {
    while (preg_match('/^\((.*)\)$/', $code)) {
      $code = substr($code, 1, -1);
    }
    parent::__construct('LeafCode:loop', $code, 'leaf');
  }

  public static function render(\Leaf\Node $Node)
  {
    $type = $Node->getAttributeNS(\Leaf\Stream::NS, 'type');
    $html = array(sprintf('<?php %s(%s): ?>', $type, $Node->firstChild->textContent));

    for ($i = 1; $i < $Node->childNodes->length; $i++) {
      $html[] = $Node->childNodes->item($i)->__toHtml();
    }
    $html[] = '<?php end' . $type . '; ?>';
    return (implode('', $html));
  }
}
?>