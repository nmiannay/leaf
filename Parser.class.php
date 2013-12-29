<?php
/**
*
*/
abstract class Parser extends \SplFileObject
{

  protected $charno       = 0;
  protected $line         = '';
  protected $stack_length = 0;
  protected $stack        = array();

  const UNEXPECTED_TOKEN = 1;
  const UNEXPECTED_EOL   = 2;

  abstract protected function addToStack($Node, $depth);
  public function __construct($filename)
  {
    parent::__construct($filename);
    $this->setFlags(SplFileObject::DROP_NEW_LINE);
    $this->line = parent::current();
  }

  protected function eatSequence($seq)
  {
    for ($i = 0; isset($req[$i]); $i++) {
      $this->eat($req[$i]);
    }
  }

  /*
  * @brief Eat a character if it appear in the $accept string
  */
  protected function eat($accept = null)
  {
    if (isset($this->line[$this->charno])) {
      if ($accept !== null && !strpbrk($this->line[$this->charno], $accept)) {
        throw new \Exception(sprintf("Parse error: syntax error on line %d, unexpected '%s'", $this->key() + 1, $this->line[$this->charno]), Parser::UNEXPECTED_TOKEN);
      }
      return ($this->line[$this->charno++]);
    }
    return (PHP_EOL);
  }

  /*
  * @brief Eat a character if it doesn't appear in the $accept string
  */
  protected function puke($accept = null)
  {
    if (isset($this->line[$this->charno])) {
      if ($accept !== null && strpbrk($this->line[$this->charno], $accept)) {
        throw new \Exception(sprintf("Parse error: syntax error on line %d, unexpected '%s'", $this->key() + 1, $this->line[$this->charno]), Parser::UNEXPECTED_TOKEN);
      }
      return ($this->line[$this->charno++]);
    }
    return (PHP_EOL);
  }

  /*
  * @brief Eat character until it appear in the $until string
  */
  protected function eatUntil($until, $accept = null)
  {
    $str = '';
    $c   = $this->eat($accept);

    while ($c !== PHP_EOL && !strpbrk($c, $until)) {
      $str .= $c;
      $c = $this->eat($accept);
    }
    if ($c === PHP_EOL && !strpbrk(PHP_EOL, $until)) {
      throw new \Exception(sprintf("Parse error: syntax error on line %d, unexpected end of line", $this->key() + 1), Parser::UNEXPECTED_EOL);
    }
    return ($str);
  }
  /*
  * @brief Eat character while it appear in the $while string
  */
  protected function eatWhile($while)
  {
    $str = '';
    $_c  = $this->lookAhead();

    while ($_c !== PHP_EOL && strpbrk($_c, $while) !== false) {
      $str .= $this->eat();
      $_c = $this->lookAhead();
    }
    return ($str);
  }

  /*
  * @brief Trim string from left while character is in array
  */
  protected function trim($trim)
  {
    $counter = 0;
    $_c      = $this->lookAhead();

    while ($_c !== PHP_EOL && strpbrk($_c, $trim) !== false) {
      $this->eat();
      $_c = $this->lookAhead();
      $counter++;
    }
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
    return (isset($this->line[$this->charno - $n]) ? $this->line[$this->charno - $n] : false);
  }

  public function next()
  {
    $this->charno = 0;
    do
    {
      parent::next();
      $this->line = parent::current();
    } while($this->line == '' && !parent::eof());
  }

  public function rewind()
  {
    parent::rewind();
    $this->charno = 0;
    $this->line = parent::current();
    if ($this->line == '') {
      $this->next();
    }
  }
}
?>