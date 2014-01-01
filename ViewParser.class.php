<?php
class ViewParser extends Parser
{
  const TAB_INDENT = 2;

  private $View;
  private $prev_indent;

  public function __construct($content)
  {
    parent::__construct($content);
    $this->View        = new View();
    $this->prev_indent = 0;
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
    }
    $this->prev_indent = $indent;

    return ($Node);
  }

  private  function parseTag()
  {
    $tagName    = $this->eatUntil('.# ' . PHP_EOL) ?: 'div';
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
          $Node = $this->View->getTagsManager()->buildNode($tagName, $text, $attributes);
          $Node->appendChild($this->parseEcho());

          return ($Node);
        }
        else {
          $attributes[$text][] = $this->parseAttrValue();
          $text = '';
          $this->trim(' ');
        }
      }
      else {
        $text .= $_c;
      }
    }

    return ($this->View->getTagsManager()->buildNode($tagName, $text, $attributes));
  }

  private function parseAttrValue()
  {
    $openChar = $this->lookAhead();
    if ($openChar != '"' && $openChar != "'") {
      return ('php echo '.$this->eatUntil(' ' . PHP_EOL).'; ');
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

    return (new \DOMProcessingInstruction ('php', 'echo ' . $text . '; '));
  }
  private  function parseCode()
  {
    return (new CodeNode('php', $this->eatUntil(PHP_EOL)));
  }
  private  function parsePureHTML()
  {
    $tagName    = $this->eatUntil(' >');
    $attributes = array();

    $this->trim(' ');
    while (($_attr = $this->eatUntil('=>'.PHP_EOL)) !== '') {
      $attributes[$_attr][] = $this->parseAttrValue();
      $this->trim(' ');
    }
    $text = $this->eatUntil(PHP_EOL);

    return ($this->View->getTagsManager()->buildNode($tagName, $text, $attributes));
  }

  private  function parseTemplating()
  {
    $blockName = $this->eatUntil(':' . PHP_EOL);
    $value     = trim($this->eatUntil(PHP_EOL), "\' ");

    return ($this->View->getTagsManager()->buildTemplate($blockName, $value));
  }
}
