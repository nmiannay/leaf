<?php
namespace Leaf\TagModifiers;

class BtnModifier extends Modifier
{


  public function apply(\DOMElement &$Tag, $alt)
  {
    $Btn     = $Tag->ownerDocument->createElement('button', $Tag->nodeValue);
    $class   = array_filter(explode(' ', $Btn->getAttribute('class')));
    $class[] = 'btn';
    if (isset($alt[1])) {
        $class[] = 'btn-'.$alt[1];
    }
    $Btn->setAttribute('class', implode(' ', $class));
    return ($Btn);
  }
}