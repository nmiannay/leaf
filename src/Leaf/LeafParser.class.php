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
    else {
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

    $this->rtrim(' ');
    while ($this->eatWhile('/^(?<name>[a-zA-Z-]+?)=(["|\'])(?<value>.*?)\2/', $attr) !== false) {
      $Node->addToAttribute($attr['name'], $attr['value']);
      $this->rtrim(' ');
    }
    if ($this->lookAhead() != '=') {
      $content = $this->eat('/(?<text>.*)$/');
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
    if ($this->lookAhead(1) == '/') {
      // var_dump([$this->input, $this->stack[$this->stack_length - 2]->localName]);
      $this->eat('/^<\/.+?>/');
      return (null);
    }
    $entity = $this->eat('/^<(?<tagName>[a-zA-Z]+)/');
    $Node   = $this->Stream->getTagsManager()->buildTag($entity['tagName']);

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
      }
    }
    return ($Node);
  }

  private  function parseTemplating()
  {
    $block    = $this->eat('/^@(?<name>[a-zA-Z]+)/');
    $Template = $this->Stream->getTagsManager()->buildTemplate($block['name']);

    if ($this->lookAhead() == ':') {
      $attr = $this->eat('/^:\s*(?<value>\'.+\'|".+"|[^"\'].*)/');
      $Template->setAttribute('value', trim($attr['value'], '"\''));
    }
    return ($Template);
  }
}
