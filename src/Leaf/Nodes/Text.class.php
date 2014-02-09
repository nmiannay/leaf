<?php
namespace Leaf\Nodes;

class Text extends \DomText
{
  public function __toHTML()
  {
    $text = htmlspecialchars($this->nodeValue, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    $text = (preg_replace('/#{\$(\w+)}/', '<?php echo $$1; ?>', $text));

    if ($this->nextSibling instanceof \DomText) {
      $text .= ' ';
    }
    return ($text);
  }
}
?>