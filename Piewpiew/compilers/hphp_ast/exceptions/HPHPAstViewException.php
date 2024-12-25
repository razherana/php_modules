<?php

namespace Piewpiew\compilers\hphp_ast\exceptions;

use Exception;

class HPHPAstViewException extends Exception
{
  public function __construct($message)
  {
    parent::__construct($message, 1);
  }
}
