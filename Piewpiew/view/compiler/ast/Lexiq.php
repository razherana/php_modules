<?php

namespace Piewpiew\view\compiler\ast;

use Piewpiew\view\compiler\AbstractASTCompiler;

class Lexiq
{
  public function __construct($name, $compiler, $regex, $content, $position, $matches)
  {
    $this->name = $name;
    $this->compiler = $compiler;
    $this->regex = $regex;
    $this->content = $content;
    $this->position = $position;
    $this->matches = $matches;
  }

  /**
   * Contains children of this lexiq
   * @var (Lexiq|TextLexiq)[] $children 
   */
  public $children = [];

  /**
   * Name of the lexiq
   * @var string
   */
  public $name;

  /**
   * The compiler of this lexiq
   * @var AbstractASTCompiler $compiler
   */
  protected $compiler;

  /** 
   * The regex to find this lexiq
   * @var string $regex
   */
  public $regex;

  /**
   * The matches of regex
   * @var string $content
   */
  public $content;

  /**
   * The position of the lexiq
   * @var int $position
   */
  public $position;

  /**
   * Matches of the regex
   * @var array<int, string> $matches
   */
  public $matches;

  public function replace($new_content)
  {
    $this->content = $new_content;
  }
}
