<?php
namespace Leaf\TagStrategies;

class DefaultStrategy extends Strategy
{

  private static $self_closing = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'base');

  public function apply(\DOMDocument $Dom, $tagName, $textContent = null, array $attributes = array())
  {
    $Node = $Dom->createElement($tagName);

    foreach ($attributes as $key => $value) {
      ($value == '') ?: $Node->setAttribute($key, implode(' ', $value));
    }
    if ($textContent != '') {
      $Node->appendChild(new \DOMText($textContent));
    }
    else if (!in_array($tagName, self::$self_closing)) {
      $Node->appendChild(new \DOMText($textContent));
    }

    return ($Node);
  }
}