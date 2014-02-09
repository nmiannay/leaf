<?php
namespace Leaf\TagModifiers;

abstract class Modifier
{
  abstract public function apply(\DOMElement &$Tag, $alt);
}