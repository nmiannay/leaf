<?php
namespace Tags\CodeStrategies;
/**
*
*/
class DefaultStrategy extends Strategy
{

  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    $str = ($code == '') ? $type : ($type . ' ' . $code);

    if (substr($code, -1, 1) == ';') {
      return (new \Tags\CodeNodes\PhpNode ($str));
    }
    return (new \Tags\CodeNodes\PhpNode ($str . ';'));
  }
}
?>