<?php
namespace Leaf\TagStrategies;

abstract class Strategy
{
  abstract public function apply(\DOMDocument $Dom, $tagName, $textContent = null, array $attributes = array());
}