<?php
namespace Leaf\Nodes\Template;

class Common extends \Leaf\Node
{
  public $blockName;

  public function __construct($blockName)
  {
    $this->blockName = $blockName;
    parent::__construct('leaf:'.$blockName, null, 'leaf');
  }

  public function __toHTML()
  {
    $html = array();

    foreach ($this->childNodes as $Node) {
      $html[] = $Node->__toHtml();
    }
    return (implode('', $html));
  }
}
?>