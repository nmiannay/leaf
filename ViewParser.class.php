<?php
class ViewParser extends Parser
{
  const TAB_INDENT = 2;

  private $View;

  public function __construct($content)
  {
    parent::__construct($content);
    $this->View = new View();
  }
  protected function addToStack($Node, $depth)
  {
    // var_dump([$depth, isset( $Node->tagName) ? $Node->tagName : $Node->wholeText ]);
    while ($this->stack_length > $depth / ViewParser::TAB_INDENT) {
      $this->stack_length--;
    }
    if ($depth == 0) {
        $this->View->getDom()->appendChild($Node);
    }
    else {
      $this->stack[$this->stack_length - 1]->appendChild($Node);
    }
    $this->stack[$this->stack_length++] = $Node;
  }

  public static function parseFile($filename)
  {
    $_instance   = new ViewParser($filename);
    $indent = $prev_indent = 0;
    $tagstack = array();

    for ($_instance->rewind(); $_instance->current() !== false; $_instance->next())
    {
      $prev_indent = $indent;
      $indent = $_instance->trim(' ');
      if ($indent % ViewParser::TAB_INDENT != 0 || ($prev_indent - $indent <= 0 && $indent + ViewParser::TAB_INDENT == $prev_indent)) {
        throw new \Exception(sprintf("Malformed indetation on line %d", $_instance->key() + 1), 1);
      }
      switch ($_instance->lookAhead()) {
        case '|':
          $_instance->eat('|');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $_instance->addToStack($_instance->parseText(), $indent);
        break;
        case '=':
          $_instance->eat('=');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $_instance->addToStack($_instance->parseEcho(), $indent);
        break;
        case '@':
          $_instance->eat('@');
          $_instance->addToStack($_instance->parseTemplating(), $indent);
        break;
        case '-':
          $_instance->eat('-');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $_instance->addToStack($_instance->parseCode(), $indent);
        break;
        case '<':
          $_instance->eat('<');
          if ($_instance->lookAhead() != '/')
            $_instance->addToStack($_instance->parsePureHTML(), $indent);
        break;
        default:
          $_instance->addToStack($_instance->parseTag(), $indent);
        break;
      }
    }
    return($_instance->View);
  }

  private  function parseTag()
  {
    $Node       = $this->View->getDom()->createElement($this->eatUntil('.# '.PHP_EOL) ?: 'div');
    $attributes = array();
    $text       = '';

    for ($_lb = $this->lookBehind(); $_lb == '.' || $_lb == '#'; $_lb = $this->lookBehind())
    {
      if (($_val = $this->eatUntil('.# '.PHP_EOL)) != '') {
        $attributes[($_lb == '.') ? 'class' : 'id'][] = $_val;
      }
    }
    $this->trim(' ');
    while (($_c = $this->eat()) !== PHP_EOL) {
      if ($_c == '=') {
        if (($_val = $this->parseAttrValue()) != '') {
          $attributes[$text][] = $_val;
        }
        $text = '';
        $this->trim(' ');
      }
      else {
        $text .= $_c;
      }
    }
    foreach ($attributes as $key => $value) {
      empty($value) ?: $Node->setAttribute($key, implode(' ', $value));
    }
    ($text == '') ?: $Node->appendChild(new \DOMText($text));
    return ($Node);
  }

  private function parseAttrValue()
  {
    return ($this->eatUntil($this->eat('"\'')));
  }

  private  function parseText()
  {
    return (new \DOMText($this->eatUntil(PHP_EOL)));
  }

  private  function parseEcho()
  {
    return (new \DOMProcessingInstruction ('php', 'echo '.$this->eatUntil(PHP_EOL).'; '));
  }
  private  function parseCode()
  {
    return (new CodeNode('php', $this->eatUntil(PHP_EOL)));
  }
  private  function parsePureHTML()
  {
    $type = $this->eatUntil(' >');
    $Node       = $this->View->getDom()->createElement($type);
    $attributes = array();

    $this->trim(' ');
    while (($_attr = $this->eatUntil('='.PHP_EOL)) !== '') {
      if (($_val = $this->parseAttrValue()) != '') {
        $attributes[$_attr][] = $_val;
      }
      $this->trim(' ');
    }
    foreach ($attributes as $key => $value) {
      empty($value) ?: $Node->setAttribute($key, implode(' ', $value));
    }
    return ($Node);
  }

  private  function parseTemplating()
  {
    $type    = $this->eatUntil(':'.PHP_EOL);
    $value   = trim($this->eatUntil(PHP_EOL));
    $TplNode = $this->View->getDom()->createElementNS('http://xyz', 'tpl:'.$type);

    $TplNode->setAttribute('value', trim($value, '"\''));
    return ($TplNode);
  }
}
