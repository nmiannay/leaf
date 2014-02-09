<?php
namespace Leaf\Nodes\Code;

class Buffered extends \Leaf\Node
{

  public function __construct($output)
  {
    parent::__construct('LeafCode:buffered', $output, 'leaf');
  }

  public static function render(\Leaf\Node $Node)
  {
    return (sprintf('<?php echo %s; ?>', $Node->nodeValue));
  }
}
