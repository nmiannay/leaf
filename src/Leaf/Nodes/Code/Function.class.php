<?php
namespace Leaf\Nodes\Code;

class Function extends Common
{
  public function apply(\Leaf\Nodes\Document $Dom, $type, $code)
  {
    return (new \Tags\CodeNodes\PhpNode($type . ' '. $code . '{', '}'));
  }
}
?>