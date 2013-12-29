<?php
namespace Tags\Strategies;

class ScriptStrategy extends TagStrategy
{
  public function apply(\Tags\Tag $Tag)
  {
    $Tag->appendChild(new \DOMText(''));
  }
}
