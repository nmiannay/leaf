<?php
namespace Leaf\Nodes;

class Text extends \DomText
{
  public function __toHTML()
  {
    $text = htmlspecialchars($this->nodeValue, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    return (preg_replace('/#{\$(\w+)}/', '<?php echo $$1; ?>', $text));
  }
}
?>