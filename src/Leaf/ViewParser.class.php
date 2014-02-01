<?php
namespace Leaf;

class ViewParser extends Parser
{
  const TAB_INDENT = 2;

  private $ViewStream;
  private $prev_indent;

  public function __construct(ViewStream $ViewStream)
  {
    parent::__construct($ViewStream->getFilename());
    $this->ViewStream  = $ViewStream;
    $this->prev_indent = 0;

    for ($this->rewind(); $this->current() !== false; $this->next()) {
      $this->parseLine();
    }
  }

  protected function addToStack($Node, $depth)
  {
    while ($this->stack_length > $depth / ViewParser::TAB_INDENT) {
      $this->stack_length--;
    }
    if ($depth == 0) {
        $this->ViewStream->getDom()->appendChild($Node);
    }
    else {
      $this->stack[$this->stack_length - 1]->appendChild($Node);
    }
    $this->stack[$this->stack_length++] = $Node;
  }

  private function parseLine(){

    $Node        = null;
    $indent      = $this->trim(' ');

    if ($indent % ViewParser::TAB_INDENT != 0 || ($this->prev_indent - $indent <= 0 && $indent + ViewParser::TAB_INDENT == $this->prev_indent)) {
      throw new \Exception(sprintf("Malformed indetation on line %d", $this->key() + 1), 1);
    }
    switch ($this->lookAhead()) {
      case '/':
      break;
      case '|':
        $this->eat('|');
        $this->lookAhead() != ' ' ?: $this->eat();
        $Node = $this->parseText();
      break;
      case '=':
        $this->eat('=');
        $this->lookAhead() != ' ' ?: $this->eat();
        $Node = $this->parseEcho();
      break;
      case '@':
        $this->eat('@');
        $Node = $this->parseTemplating();
      break;
      case '-':
        $this->eat('-');
        $this->lookAhead() != ' ' ?: $this->eat();
        $Node = $this->parseCode($indent);
      break;
      case '<':
        $this->eat('<');
        if ($this->lookAhead() != '/') {
          $Node = $this->parsePureHTML();
        }
      break;
      default:
        $Node = $this->parseTag();
      break;
    }
    if ($Node !== null) {
      $this->addToStack($Node, $indent);
    }
    $this->prev_indent = $indent;

    return ($Node);
  }

  private  function parseTag()
  {
    $tagName    = $this->eatUntil('.#> ' . PHP_EOL) ?: 'div';
    $attributes = array();
    $text       = '';

    for ($_lb = $this->lookBehind(); $_lb == '.' || $_lb == '#'; $_lb = $this->lookBehind())
    {
      if (($_val = $this->eatUntil('.# ' . PHP_EOL)) != '') {
        $attributes[($_lb == '.') ? 'class' : 'id'][] = $_val;
      }
    }
    $this->trim(' ');
    while (($_c = $this->eat()) !== PHP_EOL) {
      if ($_c == '=') {
        if ($text == '') {
          $Node = $this->ViewStream->getTagsManager()->buildNode($tagName, $text, $attributes);
          $this->trim(' ');
          $Node->appendChild($this->parseEcho());

          return ($Node);
        }
        else {
          $attributes[trim($text)][] = $this->parseAttrValue();
          $text = '';
          $this->trim(' ');
        }
      }
      else {
        $text .= $_c;
      }
    }

    return ($this->ViewStream->getTagsManager()->buildNode($tagName, $text, $attributes));
  }

  private function parseAttrValue()
  {
    $openChar = $this->lookAhead();
    if ($openChar != '"' && $openChar != "'") {
      return ($this->eatUntil(' '.PHP_EOL));
    }
    $this->eat();

    return ($this->eatUntil($openChar));
  }

  private  function parseText()
  {
    $str      = '';
    $Fragment = $this->ViewStream->getDom()->createDocumentFragment();

    while (($_c = $this->eat()) !== PHP_EOL) {
      if ($_c == '#' && $this->lookAhead() == '{') {
        $this->eat();
        $Fragment->appendChild(new \DOMText($str));
        $Fragment->appendChild(new \Leaf\CodeNodes\PhpNode('echo ' . $this->eatUntil('}') . ';'));
        $str = '';
      }
      else if ($_c == '&' && ctype_alpha($this->lookAhead())) {
        for ($reference = $this->eat(); ctype_alpha($this->lookAhead()); $this->eat()) {
          $reference .= $this->lookAhead();
        }
        if ($this->lookAhead() == ';') {
          $this->eat();
          $Fragment->appendChild(new \DOMText($str));
          $Fragment->appendChild(new \DOMEntityReference($reference));
          $str = '';
        }
        else {
          $str .= '&'.$reference;
        }
      }
      else {
        $str .= $_c;
      }
    }
    if ($str != '') {
      $Fragment->appendChild(new \DOMText($str));
    }
    return ($Fragment);
  }

  private  function parseEcho()
  {
    $text = $this->eatUntil(PHP_EOL);

    return (new \DOMProcessingInstruction ('php', 'echo ' . $text . ';'));
  }
  private  function parseCode(&$indent)
  {
    $type = $this->eatUntil(' ('.PHP_EOL);

    if ($type !== '') {
      $this->trim(' ');
      return ($this->ViewStream->getTagsManager()->buildCode($type, $this->eatUntil(PHP_EOL), $indent));
    }

    return (null);
  }
  private  function parsePureHTML()
  {
    $tagName    = $this->eatUntil(' />');
    $attributes = array();

    $this->trim(' ');
    if ($this->lookBehind() != '>') {
      while (($_attr = $this->eatUntil('/>='.PHP_EOL)) !== '') {
        $attributes[$_attr][] = $this->parseAttrValue();
        $this->trim(' ');
      }
    }
    if ($this->lookAhead() == '>') {
      $this->eat();
    }

    return ($this->ViewStream->getTagsManager()->buildNode($tagName, $this->eatUntil('<'.PHP_EOL), $attributes));
  }

  private  function parseTemplating()
  {
    $blockName = $this->eatUntil(':' . PHP_EOL);
    $value     = trim($this->eatUntil(PHP_EOL), "\' ");

    return ($this->ViewStream->getTagsManager()->buildTemplate($blockName, $value));
  }
}
