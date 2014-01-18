<?php
namespace Tags\CodeStrategies;
/**
*
*/
class DefaultStrategy extends Strategy
{

  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    if (substr($code, -1, 1) == ';') {
      return (new \Tags\CodeNodes\PhpNode ($type . ' ' . $code));
    }
    return (new \Tags\CodeNodes\PhpNode ($type . ' ' . $code.';'));
  }
}
?>