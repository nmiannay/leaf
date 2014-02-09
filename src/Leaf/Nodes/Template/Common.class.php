<?php
namespace Leaf\Nodes\Template;

class Common extends \Leaf\Node
{
  public $blockName;

  public function __construct($blockName)
  {
    $this->blockName = $blockName;
    parent::__construct('LeafTemplate:'.$blockName, null, 'leaf');
  }

  public static function render(\Leaf\Node $Node)
  {
    $html = array();

    foreach ($Node->childNodes as $Child) {
      $html[] = $Child->__toHtml();
    }
    return (implode('', $html));
  }
}
?>