<?php
namespace Leaf\Nodes\Code;
/**
*
*/
class Loop extends Common
{
  public $type;

  public function __construct($type, $code, &$indent)
  {
    $this->type  = $type;
    parent::__construct($code);

    while (preg_match('/^\((.*)\)$/', $code)) {
      $this->code = substr($code, 1, -1);
    }
  }
/*  public function __toHtml()
  {
    $html = array("<?php $this->type($this->code):?>");

    foreach ($this->childNodes as $Node) {
      $html[] = $Node->__toHtml();
    }
    $html[] = "<?php end$this->type;?>";
    return (implode('', $html));
  }*/

}
?>