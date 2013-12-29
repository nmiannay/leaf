<?php
namespace Tags\Strategies;

class DoctypeStrategy extends TagStrategy
{
  public function apply(\Tags\Tag $Tag)
  {
    $DOMImplementation = new \DOMImplementation();

    $Tag->ownerDocument->appendChild($DOMImplementation->createDocumentType($Tag->nodeValue));
    $Tag->ownerDocument->removeChild($Tag->ownerDocument->doctype);
    $Tag->ownerDocument->removeChild($Tag);
  }
}