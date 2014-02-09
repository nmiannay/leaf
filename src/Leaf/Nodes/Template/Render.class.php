<?php
namespace Leaf\Nodes\Template;

class Render extends Common
{
  public static function render(\Leaf\Node $Node)
  {
    $ChildStream = new \Leaf\Stream();
    $value       = $Node->getAttribute("value");

    return (sprintf('<?php include "%s://%s"; ?>', \Leaf\Stream::SCHEME, $value));
  }
}
