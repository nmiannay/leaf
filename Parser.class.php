<?php
/**
*
*/
abstract class Parser
{
  private $content;
  private $line_nb      = 0;
  private $char_nb      = 0;
  // private $FileObject;

  protected $stack_length = 0;
  protected $stack        = array();
  protected $inCode       = true;

  const UNEXPECTED_TOKEN = 1;
  const UNEXPECTED_EOF   = 2;
  const UNEXPECTED_EOL   = 3;
  const ILLEGAL_OFFSET   = 4;
  const EOF              = -2;

  abstract protected function addToStack($Node, $depth);
  protected function __construct($content)
  {
    $this->content = $content;
  }

  protected static function fileParser($filename)
  {
    // $this->FileObject = new SplFileObject($filename);
    // $this->FileObject->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
    if (!file_exists($filename)) {
      throw new \Exception(sprintf("Error cannot open file '%s'", $filename), 1);
    }
    if (!is_readable($filename)) {
      throw new \Exception(sprintf("Error cannot read file '%s'", $filename), 1);
    }
    return (@file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
  }
  protected static function strParser($str)
  {
    return (@file($str, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
  }

  protected function eatSequence($seq)
  {
    for ($i = 0; isset($req[$i]); $i++)
    {
      $this->eat($req[$i]);
    }
  }

  protected function eat($accept = null)
  {
    if (isset($this->content[$this->line_nb][$this->char_nb])) {
      if ($accept !== null && !strpbrk($this->content[$this->line_nb][$this->char_nb], $accept)) {
        throw new \Exception(sprintf("Parse error: syntax error, unexpected '%s'", $this->content[$this->line_nb][$this->char_nb]), Parser::UNEXPECTED_EOL);
      }
      return ($this->content[$this->line_nb][$this->char_nb++]);
    }
    return (PHP_EOL);
  }

  protected function puke($accept = null)
  {
    if (isset($this->content[$this->line_nb][$this->char_nb])) {
      if ($accept !== null && strpbrk($this->content[$this->line_nb][$this->char_nb], $accept)) {
        throw new \Exception(sprintf("Parse error: syntax error, unexpected '%s'", $this->content[$this->line_nb][$this->char_nb]), Parser::UNEXPECTED_EOL);
      }
      return ($this->content[$this->line_nb][$this->char_nb++]);
    }
    return (PHP_EOL);
  }

  protected function eatUntil($until, $accept = null)
  {
    $str = '';
    $c = $this->eat($accept);
    while ($c !== PHP_EOL && !strpbrk($c, $until))
    {
      $str .= $c;
      $c = $this->eat($accept);
    }
    if ($c === PHP_EOL && !strpbrk(PHP_EOL, $until)) {
      // var_dump(substr($this->content[$this->line_nb], $this->char_nb), $until, $this->lookBehind());
      throw new \Exception(sprintf("Parse error: syntax error, unexpected end of line %d", $this->line_nb + 1), Parser::UNEXPECTED_EOL);
    }
    return ($str);
  }
  /*
  * @brief Eat char while it is in while string
  */
  protected function eatWhile($while)
  {
    $str = '';
    $_c  = $this->lookAhead();

    while ($_c !== PHP_EOL && strpbrk($_c, $while) !== false)
    {
      $str .= $this->eat();
      $_c = $this->lookAhead();
    }
    return ($str);
  }
  /*
  * @brief Skip char while it is in trim string
  */
  protected function trim($trim)
  {
    $counter = 0;
    $_c      = $this->lookAhead();

    while ($_c !== PHP_EOL && strpbrk($_c, $trim) !== false)
    {
      $this->eat();
      $_c = $this->lookAhead();
      $counter++;
    }
    return ($counter);
  }
  /*
  * @brief Look to the next char
  */
  protected function lookAhead($n = 0)
  {
    if (isset($this->content[$this->line_nb][$this->char_nb + $n])) {
      return ($this->content[$this->line_nb][$this->char_nb + $n]);
    }
    return (PHP_EOL);
  }
  /*
  * @brief Rewind current position
  */
  protected function rewind($n = 1)
  {
    if (isset($this->content[$this->line_nb][$this->char_nb - $n])) {
      $this->char_nb -= $n;
      return ($this->content[$this->line_nb][$this->char_nb]);
    }
    return (PHP_EOL);
  }
  /*
  * @brief Look to the previous char
  */
  protected function lookBehind($n = 1)
  {
    if (isset($this->content[$this->line_nb][$this->char_nb - $n])) {
      return ($this->content[$this->line_nb][$this->char_nb - $n]);
    }
    return (false);
  }
  /*
  * @brief Get next line
  */
  protected function getNextLine()
  {
    if (isset($this->content[$this->line_nb + 1])) {
      $this->char_nb = 0;
      return ($this->content[++$this->line_nb]);
    }
    return (Parser::EOF);
  }
  /*
  * @brief Get current line
  */
  protected function getLine()
  {
    if (isset($this->content[$this->line_nb])) {
      return ($this->content[$this->line_nb]);
    }
    return (Parser::EOF);
  }


}
?>