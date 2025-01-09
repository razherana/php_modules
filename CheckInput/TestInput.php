<?php

namespace CheckInput;

use Closure;

class TestInput {
  /**
   * The name as noted in the config
   * @var string
   */
  public $name = '';

  /**
   * The closure to check as noted in the config
   * @var Closure
   */
  public $closure;

  /**
   * The vars found after regex
   * @var string[]
   */
  public $vars = [];

  /**
   * The regex used
   * @var string
   */
  public $regex = '';

  /**
   * @param Closure $closure
   */
  public function __construct($name, $closure, $vars, $regex)
  {
    $this->name = $name;
    $this->closure = $closure->bindTo($this);
    $this->vars = $vars;
    $this->regex = $regex;
  }
}