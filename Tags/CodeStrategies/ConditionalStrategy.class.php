<?php
namespace Tags\CodeStrategies;

class ConditionalStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $type, $code, &$indent)
  {
    if ($type == 'else') {
      $indent += 2;
      return (new \DOMProcessingInstruction ('php', 'else: '));
    }
    while (preg_match('/^\((.*)\)$/', $code)) {
      $code = substr($code, 1, -1);
    }
    return (new \Tags\CodeNodes\ScopedNode($type . ' (' . $code . '):', 'endif'));
  }
}
