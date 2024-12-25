<?php

namespace Piewpiew\view\compiler\ast;

use Piewpiew\view\compiler\AbstractASTCompiler;

abstract class AbstractTermEvent {
  /** @var string $name */
  protected $name;

  /** @var AbstractDictionary */
  protected $dictionary;

  /** @var AbstractASTCompiler */
  protected $compiler;

  /**
   * The closure with the condition of this TermEvent
   * @var \Closure
   */
  protected $condition;

  /** 
   * Contains the index of start in the lexiqs array
   * @var int
   */
  protected $index;

  /** @var (Lexiq|TextLexiq)[] */
  protected $lexiqs;

  public function __construct($name, $dictionary, $compiler, $condition, $index, &$lexiqs)
  {
    $this->name = $name;
    $this->dictionary = $dictionary;
    $this->compiler = $compiler;
    $this->condition = $condition;
    $this->index = $index;
    $this->lexiqs = $lexiqs;
  }

  /**
   * This method gets back all of the lexiqs 
   * modified and reassigned to be used again for other Events.
   */
  abstract public function return_lexiqs() : array;

  /**
   * Skips how many lexiqs after handling
   */
  public function return_skips() : int {
    return 1;
  }
}