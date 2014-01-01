<?php
namespace Tags\TemplateStrategies;

class RenderStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $blockName, $value, array $options = array())
  {
    $View       = \View::fromFile($value);
    $IncludeDom = $View->getDom();
    $TplNode    = $Dom->createDocumentFragment();

    for ($Child = $IncludeDom->firstChild; $Child !== null; $Child = $Child->nextSibling) {
      $TplNode->appendChild($Dom->importNode($Child, true));
    }

    return ($TplNode);
  }
}
