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
      $value = htmlentities($Attribute->value, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
      $value = (preg_replace('/&num;&lbrace;&dollar;(\w+)&rcub;/', '<?php echo $$1; ?>', $value));
      $str[] = sprintf('%s="%s"', $Attribute->name, $value);
    }
    return (implode(' ', $str));
  }

  public function __toHTML()
  {
    return ($this->ownerDocument->getManager()->renderElement($this));
  }
}
?>