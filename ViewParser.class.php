<?php
class ViewParser extends Parser
{
  const TAB_INDENT = 2;

  private $View;

  protected function __construct($content, $View = null)
  {
    parent::__construct($content);
    $this->View = ($View) ?: new View();
  }
  protected function addToStack($Node, $depth)
  {
    while ($this->stack_length > $depth / ViewParser::TAB_INDENT)
    {
      $this->stack_length--;
    }
    if ($depth == 0) {
        $this->View->appendChild($Node);
    }
    else {
      $this->stack[$this->stack_length - 1]->appendChild($Node);
    }
    $this->stack[$this->stack_length++] = $Node;
  }

  public static function parseFile($filename)
  {
    $_instance = new ViewParser(parent::fileParser($filename));
    $line      = trim($_instance->getLine());
    $Node      = null;
    while ($line !== Parser::EOF)
    {
      $indent = $_instance->trim(' ');
      switch ($_instance->lookAhead()) {
        case PHP_EOL:
        break;
        case '|':
          $_instance->eat('|');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $Node = $_instance->parseText();
        break;
        case '=':
          $_instance->eat('=');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $Node = $_instance->parseEcho();
        break;
        case '@':
          $_instance->eat('@');
          $Node = $_instance->parseTemplating();
        break;
        case '-':
          $_instance->eat('-');
          $_instance->lookAhead() != ' ' ?: $_instance->eat();
          $Node = $_instance->parseCode();
        break;
        default:
          $Node = $_instance->parseTag();
        break;
      }
      $Node->depth = $indent;
      $_instance->addToStack($Node, $indent);
      $line = $_instance->getNextLine();
    }
    return($_instance->View);
  }

  private  function parseTag()
  {
    $Node       = $this->View->createElement($this->eatUntil('.# '.PHP_EOL) ?: 'div');
    $attributes = array();
    $text       = '';

    for ($_lb = $this->lookBehind(); $_lb == '.' || $_lb == '#'; $_lb = $this->lookBehind())
    {
      if (($_val = $this->eatUntil('.# '.PHP_EOL)) != '') {
        $attributes[($_lb == '.') ? 'class' : 'id'][] = $_val;
      }
    }
    $this->trim(' ');
    while (($_c = $this->eat()) !== PHP_EOL)
    {
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
      (empty($value)) ? : $Node->setAttribute($key, implode(' ', $value));
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
  private  function parseTemplating()
  {
    $type      = $this->eatUntil(':'.PHP_EOL);
    $value     = trim($this->eatUntil(PHP_EOL));
    $TplNode = $this->View->createElementNS('http://xyz', 'tpl:'.$type);
    $TplNode->setAttribute('value', trim($value, '"'));

    return ($TplNode);
  }
}
