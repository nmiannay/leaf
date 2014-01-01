<?php
namespace Tags\TemplateStrategies;

class DefaultStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $blockName, $value, array $options = array())
  {
    $TplNode = $Dom->createElementNS('http://xyz', 'tpl:' . $blockName);
    $TplNode->setAttribute('value', $value);

    return ($TplNode);
  }
}