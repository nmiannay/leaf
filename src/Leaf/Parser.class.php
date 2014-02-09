<?php
namespace Leaf;
/**
*
*/
abstract class Parser extends \SplFileObject
{

  protected $charno       = 0;
  protected $line         = '';
  protected $input        = '';
  protected $stack_length = 0;
  protected $stack        = array();

  const UNEXPECTED_TOKEN = 1;
  const UNEXPECTED_EOL   = 2;

  abstract protected function addToStack($Node, $depth);
  public function __construct($filename)
  {
    parent::__construct($filename);
    $this->setFlags(\SplFileObject::DROP_NEW_LINE);
    $this->line = parent::current();
  }


  protected function consumeInput($length)
  {
    $consumed    = substr($this->input, 0, (int)$length);
    $this->input = substr($this->input, (int)$length);
    $this->charno += $length;
    return ($consumed);
  }
  /*
  * @brief Eat characters if it appear they matches $accept
  */
  protected function eat($accept = null)
  {
    if ($accept === null) {
      return ($this->consumeInput(1));
    }
    elseif (preg_match($accept, $this->input, $matches)) {
      $this->consumeInput(mb_strlen(array_shift($matches)));
      // var_dump($this->dsqd());
      return ($matches);
    }
    else {
      throw new \Exception(sprintf("Parse error: syntax error on line %d, unexpected token `%s'", $this->key() + 1, $this->input), Parser::UNEXPECTED_TOKEN);
    }
  }
  protected function eatWhile($accept = null, &$matches = null)
  {
    if ($accept === null) {
      return ($this->consumeInput(1));
    }
    elseif (preg_match($accept, $this->input, $matches)) {
      $this->consumeInput(mb_strlen(array_shift($matches)));
      return ($matches);
    }
    return (false);
  }
  /*
  * @brief Trim string from left while character is in array
  */
  protected function rtrim($trim)
  {
    $counter = 0;
    while (isset($this->input[$counter]) && $this->input[$counter] == $trim) {
      $counter++;
    }
    $this->consumeInput($counter);
    return ($counter);
  }
  /*
  * @brief Look to the next character
  */
  protected function lookAhead($n = 0)
  {
    return (isset($this->line[$this->charno + $n]) ? $this->line[$this->charno + $n] : PHP_EOL);
  }
  /*
  * @brief Look to the previous character
  */
  protected function lookBehind($n = 1)
  {
    return (isset($this->line[$this->charno - $n])? $this->line[$this->charno - $n] : false);
  }

  public function next()
  {
    $this->charno = 0;
    do
    {
      parent::next();
      $this->input = $this->line = parent::current();
    } while($this->line == '' && !parent::eof());
  }

  public function rewind()
  {
    parent::rewind();
    $this->charno = 0;
    $this->input = $this->line   = parent::current();
    if ($this->line == '') {
      $this->next();
    }
  }
}
?>