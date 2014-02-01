<?php
namespace Leaf\CodeStrategies;

abstract class Strategy
{
  abstract public function apply(\DOMDocument $Dom, $type, $code, &$indent);
}