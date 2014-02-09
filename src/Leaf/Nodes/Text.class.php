<?php
namespace Leaf\Nodes;

class Text extends \DomText
{
  public function __toHTML()
  {
    $text = htmlentities($this->wholeText, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
    return (preg_replace('/&num;&lbrace;&dollar;(\w+)&rcub;/', '<?php echo $$1; ?>', $text));
  }
}
?>