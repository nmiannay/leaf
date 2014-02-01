<?php
namespace Leaf\TagStrategies;

class DoctypeStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $tagName, $textContent = null, array $attributes = array())
  {
    $DOMImplementation = new \DOMImplementation();

    if ($Dom->doctype !== null) {
      $Dom->removeChild($Dom->doctype);
    }
    return ($DOMImplementation->createDocumentType($textContent));
  }
}