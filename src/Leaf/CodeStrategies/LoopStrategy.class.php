<?php
namespace Leaf\CodeStrategies;
/**
*
*/
class LoopStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    while (preg_match('/^\((.*)\)$/', $code)) {
      $code = substr($code, 1, -1);
    }
    return (new \Leaf\CodeNodes\PhpNode($type . '(' . $code . '):', 'end'.$type));
  }
}
?>