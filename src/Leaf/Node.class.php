<?php
namespace Leaf;

class Node extends \DomElement
{

  private $childClass;
  public function __construct($tag, $content = null, $ns = null)
  {
    $this->childClass = get_called_class();
    // var_dump($this->childClass);
    parent::__construct($tag, $content, $ns);
  }
  public function addToAttribute($key, $value)
  {
    $oldVal = $this->getAttribute($key);
    $this->setAttribute($key, ($oldVal ? $oldVal . ' ' : '') . $value);
  }
  public function __toHTML()
  {
    var_dump($this->childClass);
  }
}
?>