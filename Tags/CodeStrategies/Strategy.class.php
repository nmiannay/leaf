<?php
namespace Tags\CodeStrategies;

abstract class Strategy
{
  abstract public function apply(\DOMDocument $Dom, $type, $code, &$indent);
}