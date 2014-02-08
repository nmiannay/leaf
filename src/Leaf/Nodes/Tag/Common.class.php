<?php
namespace Leaf\Nodes\Tag;


class Common extends \Leaf\Node
{
  private static $self_closing = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'base');

  protected function getAttributes_str()
  {
    $str = array();
    foreach ($this->attributes as $name => $value) {
        $str[] = sprintf('%s="%s"', $name, addslashes($value));
    }
    return (isset($str[0]) ? ' '.implode(' ', $str) : '');
  }

  public function __toHTML()
  {
    $html = array();

    if (in_array($this->tagName, self::$self_closing)) {
      return (sprintf("<$this->tagName%s />", $this->getAttributes_str()));
    }
    else {
      $html[] = sprintf("<$this->tagName%s>", $this->getAttributes_str());
      $html[] = htmlentities($this->value);
      foreach ($this->childNodes as $Node) {
        $html[] = $Node->__toHtml();
      }
      $html[] = "</$this->tagName>";
    }
    return (implode('', $html));
  }
}
?>