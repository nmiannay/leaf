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
    $_instance = new ViewParser($filename);

    for ($_instance->rewind(); $_instance->current() !== false; $_instance->next()) {
      $_instance->parseLine();
    }
    return($_instance->View);
  }

  private function parseLine(){
    static $indent = 0;

    $prev_indent = $indent;
    $Node        = null;
    $indent      = $this->trim(' ');

    if ($indent % ViewParser::TAB_INDENT != 0 || ($prev_indent - $indent <= 0 && $indent + ViewParser::TAB_INDENT == $prev_indent)) {
      throw new \Exception(sprintf("Malformed indetation on line %d", $this->key() + 1), 1);
    }
    switch ($this->lookAhead()) {
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

        $Node = $this->parseCode();
        if ($Node->data == 'else: ') {
          $this->stack_length--;
          $indent += 2;
        }
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
      if (method_exists($Node, 'apply')) {
        $Node->apply();
      }
    }
    return ($Node);
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
        if ($text == '') {
          $Node->appendChild($this->parseEcho());
        }
        else {
          if (($_val = $this->parseAttrValue()) != '') {
            $attributes[$text][] = $_val;
          }
          $text = '';
          $this->trim(' ');
        }
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
    $openChar = $this->lookAhead();
    if ($openChar != '"' && $openChar != "'") {
      return ('php echo '.$this->eatUntil(' '.PHP_EOL).'; ');
    }
    $this->eat();
    return ($this->eatUntil($openChar));
  }

  private  function parseText()
  {
    return (new \DOMText($this->eatUntil(PHP_EOL)));
  }

  private  function parseEcho()
  {
    $text = $this->eatUntil(PHP_EOL);
    return (new \DOMProcessingInstruction ('php', 'echo '.$text.'; '));
  }
  private  function parseCode()
  {
    return (new CodeNode('php', $this->eatUntil(PHP_EOL)));
  }
  private  function parsePureHTML()
  {
    $type       = $this->eatUntil(' >');
    $Node       = $this->View->getDom()->createElement($type);
    $attributes = array();

    $this->trim(' ');
    while (($_attr = $this->eatUntil('=>'.PHP_EOL)) !== '') {
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
