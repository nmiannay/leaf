<?php
namespace Leaf\TemplateStrategies;

abstract class Strategy
{
  abstract public function apply(\DOMDocument $Dom, $blockName, $value, array $options = array());
}