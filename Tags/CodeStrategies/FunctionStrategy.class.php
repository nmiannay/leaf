<?php
namespace Tags\CodeStrategies;
/**
*
*/
class FunctionStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    return (new \Tags\CodeNodes\PhpNode($type . ' '. $code . '{', '}'));
  }
}
?>