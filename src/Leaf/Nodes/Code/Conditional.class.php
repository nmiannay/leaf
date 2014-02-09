<?php
namespace Leaf\Nodes\Code;

class Conditional extends \Leaf\Node
{

  public $type;

  public function __construct($type, $code)
  {
    if ($code) {
      while (preg_match('/^\((.*)\)$/', $code)) {
        $code = substr($code, 1, -1);
      }
    }
    parent::__construct('LeafCode:conditional', $code, 'leaf');
  }

  public static function render(\Leaf\Node $Node)
  {
    $type = $Node->getAttributeNS(\Leaf\Stream::NS, 'type');
    if ($type != 'else') {
      $html  = array(sprintf('<?php %s(%s): ?>', $type, $Node->firstChild->textContent));
      $start = 1;
    }
    else {
      $html  = array(sprintf('<?php %s: ?>', $type));
      $start = 0;
    }
    for ($i = $start; $i < $Node->childNodes->length; $i++) {
      $html[] = $Node->childNodes->item($i)->__toHtml();
    }
    if (!$Node->nextSibling || ($Node->nextSibling->tagName != 'LeafCode:conditional' || $Node->nextSibling->getAttributeNS(\Leaf\Stream::NS, 'type') == 'if')) {
      $html[] = '<?php endif; ?>';
    }
    return (implode('', $html));
  }

}
