<?php
namespace Tags\TagStrategies;

class ScriptStrategy extends Strategy
{
  public function apply(\DOMDocument $Dom, $tagName, $textContent = null, array $attributes = array())
  {
    $Node = $Dom->createElement($tagName);

    foreach ($attributes as $key => $value) {
      ($value == '') ?: $Node->setAttribute($key, implode(' ', $value));
    }
    $Node->appendChild(new \DOMText($textContent));

    return ($Node);
  }
}
