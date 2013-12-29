<?php
namespace Tags;

class Tag extends \DOMElement
{
  protected static $strategies = array();

  public static function registerStrategy($tagName, Strategies\TagStrategy $Strategy)
  {
    self::$strategies[$tagName] = $Strategy;
  }

  public function apply() {
    if (isset(self::$strategies[$this->tagName])) {
      self::$strategies[$this->tagName]->apply($this);
    }
  }
}
