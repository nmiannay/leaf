<?php
namespace Leaf\Nodes;

class Manager
{
  protected $Document = null;
  protected static $Tags         = array();
  protected static $TagModifiers = array();
  protected static $Templates    = array();
  protected static $Codes        = array();

  private static $is_initialized = false;
  public function __construct(\Leaf\Document $Document) {
    $this->Document = $Document;

    if (!self::$is_initialized) {
      self::registerTagNode('doctype', 'Leaf\\Nodes\\Tag\\Doctype');

      self::registerTemplateNode('render', 'Leaf\\Nodes\\Template\\Render');

      // self::registerCodeNode('function', 'Leaf\\Nodes\\Code\\Function');
      self::registerCodeNode('foreach', 'Leaf\\Nodes\\Code\\Loop');
      self::registerCodeNode('for', 'Leaf\\Nodes\\Code\\Loop');
      self::registerCodeNode('while', 'Leaf\\Nodes\\Code\\Loop');
      self::registerCodeNode('if', 'Leaf\\Nodes\\Code\\Conditional');
      self::registerCodeNode('else', 'Leaf\\Nodes\\Code\\Conditional');
      self::registerCodeNode('elseif', 'Leaf\\Nodes\\Code\\Conditional');
      self::$is_initialized = true;
    }
    $this->Document->registerNodeClass('DomText', 'Leaf\\Nodes\\Text');
    $this->Document->registerNodeClass('DOMElement', 'Leaf\\Node');
    // foreach (self::$Tags as $class) {

    //   $this->Document->registerNodeClass('DOMElement', $class);
    // }
    // foreach (self::$Templates as $class) {
    //   $this->Document->registerNodeClass('DOMElement', $class);
    // }
    // foreach (self::$Codes as $class) {
    //   $this->Document->registerNodeClass('DOMElement', $class);
    // }
  }
  public static function registerTagNode($tagName, $class)
  {
    self::$Tags[$tagName] = $class;
  }
  public static function registerTemplateNode($blockName, $class)
  {
    self::$Templates[$blockName] = $class;
  }
  public static function registerCodeNode($type, $class)
  {
    self::$Codes[$type] = $class;
  }
  public static function registerModifiers($type, $class)
  {
    self::$TagModifiers[$type] = $class;
  }

  public function buildTag($tagName, $textContent = null) {
    // if ($pos = strpos($tagName, ':')) {
    //   $alt     = explode('-', substr($tagName, $pos + 1));
    //   $tagName = substr($tagName, 0, $pos);
    // }
    if (isset(self::$Tags[$tagName])) {
      $Tag = new self::$Tags[$tagName]($tagName, $textContent);
    }
    else {
      $Tag = new Tag\Common($tagName, $textContent);
    }
    $this->Document->appendChild($Tag);
    // if (isset($alt)) {
    //   if (isset($this->TagModifiers[$alt[0]])) {
    //     $Tag = $this->TagModifiers[$alt[0]]->apply($Tag, $alt);
    //   }
    //   else {
    //     throw new \Exception(sprintf("Unknow modifier `%s'", $alt), 1);
    //   }
    // }
    return ($Tag);
  }

  public function buildTemplate($blockName) {
    if (isset(self::$Templates[$blockName])) {
      $Tag = new self::$Templates[$blockName]($blockName);
    }
    else {
      $Tag = new Template\Common($blockName);
    }
    $this->Document->appendChild($Tag);
    return ($Tag);
  }

  public function buildCode($type, $value, &$indent) {
    if (isset(self::$Codes[$type])) {
      return (new self::$Codes[$type]($type, $value, $indent));
    }
    return (new Code\Common($type .' ' . $value));
  }
}
