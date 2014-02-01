<?php
namespace Leaf\CodeStrategies;

class ConditionalStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    if ($type == 'else') {
      $indent += 2;
      return (new \Leaf\CodeNodes\PhpNode ('else:'));
    }
    elseif ($type == 'elseif') {
      $indent += 2;
      while (preg_match('/^\((.*)\)$/', $code)) {
        $code = substr($code, 1, -1);
      }
      return (new \Leaf\CodeNodes\PhpNode($type . '(' . $code . '):'));
    }
    while (preg_match('/^\((.*)\)$/', $code)) {
      $code = substr($code, 1, -1);
    }

    return (new \Leaf\CodeNodes\PhpNode($type . '(' . $code . '):', 'endif'));
  }
}
