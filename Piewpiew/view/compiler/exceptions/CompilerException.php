<?php

namespace Piewpiew\view\compiler\exceptions;

use Exception;
use Piewpiew\view\compiler\AbstractCompiler;

/**
 * Default exception for a compiler
 */
class CompilerException extends Exception
{
  /** @var ?AbstractCompiler $compiler */
  public $compiler = null;

  public function __construct($message = "", $compiler = null)
  {
    $this->compiler = $compiler;
    parent::__construct($message);
  }
}
