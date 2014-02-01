<?php
namespace Leaf\TemplateStrategies;

class RenderStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $blockName, $value, array $options = array())
  {
    $ChildView = new \Leaf\ViewStream();
    $ChildView->stream_open(sprintf('%s://%s', \Leaf\ViewStream::SCHEME, $value), 'rb', 0, $value);
    $IncludeDom = $ChildView->getDom();
    $TplNode    = $Dom->createDocumentFragment();

    for ($Child = $IncludeDom->firstChild; $Child !== null; $Child = $Child->nextSibling) {
      $TplNode->appendChild($Dom->importNode($Child, true));
    }

    return ($TplNode);
  }
}
