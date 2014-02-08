<?php
namespace Leaf\Nodes\Code;

class Conditional extends Common
{

  public $type;

  public function __construct($type, $code, &$indent)
  {
    $this->type  = $type;
    parent::__construct($code);

    if ($this->type != 'else' && $this->type != 'elseif') {
      $indent -= 2;
    }
    if ($code) {
      while (preg_match('/^\((.*)\)$/', $code)) {
        $this->code = substr($code, 1, -1);
      }
    }
  }
/*  public function __toHtml()
  {
    if ($this->type == 'else') {
      $html = array("<?php $this->type:?>");
    }
    else {
      $html = array("<?php $this->type($this->code):?>");
    }

    foreach ($this->childNodes as $Node) {
      $html[] = $Node->__toHtml();
    }
    if ($this->type != 'else' && $this->type != 'elseif') {
      $html[] = '<?php endif;?>';
    }
    return (implode('', $html));
  }*/

}
