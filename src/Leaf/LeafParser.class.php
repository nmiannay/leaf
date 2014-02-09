<?php
namespace Leaf;

class LeafParser extends Parser
{
  const TAB_INDENT = 2;

  private $Stream;
  private $prev_indent;

  public function __construct(Stream $Stream)
  {
    parent::__construct($Stream->getFilename());
    $this->Stream      = $Stream;
    $this->prev_indent = 0;

    for ($this->rewind(); $this->current() !== false; $this->next()) {
      if ($this->current() != '') {
        $this->parseLine();
      }
    }
  }

  protected function addToStack($Node, $depth)
  {
    while ($this->stack_length > $depth / self::TAB_INDENT) {
      $this->stack_length--;
    }
    if ($depth == 0) {
        $this->Stream->getDom()->appendChild($Node);
    }
    else if ($this->stack_length > 0){
      $this->stack[$this->stack_length - 1]->appendChild($Node);
    }
    $this->stack[$this->stack_length++] = $Node;
  }

  private function parseLine(){

    $Node        = null;
    $indent      = $this->rtrim(' ');

    if ($indent % self::TAB_INDENT != 0 || ($this->prev_indent - $indent <= 0 && $indent + self::TAB_INDENT == $this->prev_indent)) {
      throw new \Exception(sprintf("Malformed indetation on line %d", $this->key() + 1), 1);
    }
    $this->prev_indent = $indent;
    while ($this->input !== false) {
      $prev_input = $this->input;
      switch ($this->lookAhead()) {
        case '/':
        break;
        case '|':
          $Node = $this->parseText();
        break;
        case '=':
          $Node = $this->parseEcho();
        break;
        case '@':
          $Node = $this->parseTemplating();
        break;
        case '-':
          $Node = $this->parseCode($indent);
        break;
        case '<':
          $Node = $this->parsePureHTML();
        break;
        default:
          $Node = $this->parseTag();
        break;
      }
      if ($Node !== null) {
        $this->addToStack($Node, $indent);
      }
      if ($prev_input == $this->input) {
        break;
      }
      $indent += 2;
    }
  }

  private  function parseTag()
  {
    $token = $this->eat('/^(?<tagName>[\w]*)/');
    $Node  = $this->Stream->getTagsManager()->buildTag($token['tagName'] ?: 'div');

    while ($this->eatWhile('/^(\.|#)(?<value>[\w-]+)/', $attr) !== false) {
      $Node->addToAttribute($attr[0] == '#' ? 'id' : 'class', $attr['value']);
    }
    $attributes = array();

    while ($this->eatWhile('/^\s*(?<name>[a-zA-Z-]+?)=(["|\'])(?<value>.*?)(?<!\\\)\2/', $attr) !== false) {
      $Node->addToAttribute($attr['name'], stripslashes($attr['value']));
    }
    if ($this->eatWhile('/^\s*=/') !== false) {
        $this->input = '=' . $this->input;

        $Node->appendChild($this->parseEcho());
    }
    elseif ($this->input !== false) {
      $content = $this->eat('/^\s+(?<text>.*)$/');

      if ($content['text']) {
        $Node->appendChild(new Nodes\Text($content['text']));
      }
    }
    return ($Node);
  }

  private  function parseText()
  {
    $content = $this->eat('/^\|\s?(?<text>.*)/');

    if (isset($content['text'])) {
      return (new Nodes\Text($content['text']));
    }
    return (null);
  }

  private  function parseEcho()
  {
    $entity = $this->eat('/^=\s*(?<output>.*);?$/');

    return (new Nodes\Code\Buffered($entity['output']));
  }
  private  function parseCode(&$indent)
  {
    $instruction = $this->eat('/^-\s*(?<type>\S+)\s*(?<attr>.*)$/');

    return ($this->Stream->getTagsManager()->buildCode($instruction['type'], $instruction['attr'], $indent));
  }
  private  function parsePureHTML()
  {
    static $open = array();

    if ($this->lookAhead(1) == '/') {
      $this->eat('/^<\/' . array_pop($open) . '>/');
      return (null);
    }
    $entity  = $this->eat('/^<(?<tagName>[a-zA-Z]+)/');
    $Node    = $this->Stream->getTagsManager()->buildTag($entity['tagName']);

    $this->rtrim(' ');
    while ($this->eatWhile('/^(?<name>[a-zA-Z-]+?)=(["|\'])(?<value>.*?)\2/', $attr) !== false) {
      $Node->addToAttribute($attr['name'], $attr['value']);
      $this->rtrim(' ');
    }
    $this->eat('/^\/?>/');
    if ($this->lookAhead() !== '<' && $this->lookAhead() !== false) {
      $content = $this->eat('/^(?<text>.*?)(<\/' . $entity['tagName'] . '>|<|$)/');

      if (isset($content['text'])) {
        $Node->appendChild(new Nodes\Text($content['text']));
        if ($content[1] == '<') {
          $this->input = '<' . $this->input;
        }
        elseif ($content[1]) {
          return ($Node);
        }
      }
    }
    $open[] = $entity['tagName'];
    return ($Node);
  }

  private  function parseTemplating()
  {
    $block    = $this->eat('/^@(?<name>[a-zA-Z]+)/');
    $Template = $this->Stream->getTagsManager()->buildTemplate($block['name']);

    if ($this->lookAhead() == ':') {
      $this->eat('/^:\s*/');
      if ($this->lookAhead() == '\'') {
        $attr = $this->eat('/^\'(?<value>.+?)(?<!\\\)\'/');
      }
      elseif ($this->lookAhead() == '"') {
        $attr = $this->eat('/^"(?<value>.+?)(?<!\\\)"/');
      }
      else {
        $attr = $this->eat('/^(?<value>.*)/');
      }
      $Template->setAttribute('value', stripslashes($attr['value']));
    }
    return ($Template);
  }
}
