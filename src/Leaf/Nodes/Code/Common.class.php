<?php
namespace Leaf\Nodes\Code;
/**
*
*/
class Common extends \Leaf\Node
{
  protected $code = null;

  public function __construct($code)
  {
    $this->code = $code;
    parent::__construct('leaf:code', null, 'leaf');
  }

  public function __toHTML()
  {
    $html   = array('<?php ');
    $html[] = trim($this->code, ';').';';
    foreach ($this->childNodes as $Node) {
      $html[] = $Node->__toHtml();
    }
    $html[] = '?>';
    return (implode('', $html));
  }
}