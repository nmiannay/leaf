<?php
namespace Leaf\Nodes\Code;

class Function extends Common
{
  public function apply(\Leaf\Nodes\Document $Dom, $type, $code, &$indent)
  {
    return (new \Tags\CodeNodes\PhpNode($type . ' '. $code . '{', '}'));
  }
}
?>