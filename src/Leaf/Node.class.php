<?php
namespace Leaf;

class Node extends \DomElement
{

  public function __construct($tag, $content = null, $ns = null)
  {
    parent::__construct($tag, $content, $ns);
  }
  public function addToAttribute($key, $value)
  {
    $oldVal = $this->getAttribute($key);
    $this->setAttribute($key, ($oldVal ? $oldVal . ' ' : '') . $value);
  }
  protected function getAttributes_str()
  {
    $str = array();

    foreach ($this->attributes as $Attribute) {
      $value = preg_replace('/#{\$(\w+)}/', '<?php echo $$1; ?>', $Attribute->value);
      $str[] = sprintf('%s="%s"', $Attribute->name, $value);
    }
    return (isset($str[0]) ? ' '.implode(' ', $str) : '');
  }

  public function __toHTML()
  {
    return ($this->ownerDocument->getManager()->renderElement($this));
  }
}
?>