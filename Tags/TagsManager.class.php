<?php
namespace Tags;

class TagsManager
{
  protected $Dom;
  protected $DefaultStrategy;
  protected $DefaulTemplateStrategy;
  protected $DefaulCodeStrategy;
  protected $TagStrategies      = array();
  protected $TemplateStrategies = array();
  protected $CodeStrategies     = array();

  public function __construct(\DOMDocument $Dom)
  {
    $this->Dom                    = $Dom;
    $this->DefaultStrategy        = new TagStrategies\DefaultStrategy();
    $this->DefaulTemplateStrategy = new TemplateStrategies\DefaultStrategy();
    $this->DefaulCodeStrategy     = new CodeStrategies\DefaultStrategy();
  }

  public function registerStrategy($tagName, TagStrategies\Strategy $Strategy)
  {
    $this->TagStrategies[$tagName] = $Strategy;
  }
  public function registerTempalateStrategy($blockName, TemplateStrategies\Strategy $Strategy)
  {
    $this->TemplateStrategies[$blockName] = $Strategy;
  }
  public function registerCodeStrategy($type, CodeStrategies\Strategy $Strategy)
  {
    $this->CodeStrategies[$type] = $Strategy;
  }

  public function buildNode($tagName, $textContent = null, array $attributes = array()) {
    if (isset($this->TagStrategies[$tagName])) {
      return ($this->TagStrategies[$tagName]->apply($this->Dom, $tagName, $textContent, $attributes));
    }
    return ($this->DefaultStrategy->apply($this->Dom, $tagName, $textContent, $attributes));
  }

  public function buildTemplate($blockName, $value, array $options = array()) {
    if (isset($this->TemplateStrategies[$blockName])) {
      return ($this->TemplateStrategies[$blockName]->apply($this->Dom, $blockName, $value, $options));
    }
    return ($this->DefaulTemplateStrategy->apply($this->Dom, $blockName, $value, $options));
  }

  public function buildCode($type, $value, &$indent) {
    if (isset($this->CodeStrategies[$type])) {
      return ($this->CodeStrategies[$type]->apply($this->Dom, $type, $value, $indent));
    }
    return ($this->DefaulCodeStrategy->apply($this->Dom, $type, $value, $indent));
  }
}
