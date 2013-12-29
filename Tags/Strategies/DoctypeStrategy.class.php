<?php
namespace Tags\Strategies;

class DoctypeStrategy extends TagStrategy
{
  public function apply(\Tags\Tag $Tag)
  {
    $DOMImplementation = new \DOMImplementation();

    if ($Tag->ownerDocument->doctype !== null) {
      $Tag->ownerDocument->removeChild($Tag->ownerDocument->doctype);
    }
    $Tag->ownerDocument->appendChild($DOMImplementation->createDocumentType($Tag->nodeValue));
    $Tag->ownerDocument->removeChild($Tag);
  }
}