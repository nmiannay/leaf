<?php
namespace Tags\CodeStrategies;
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
    return (new \Tags\CodeNodes\PhpNode($type . ' (' . $code . '):', 'end'.$type));
  }
}
?>