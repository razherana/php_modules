<?php

namespace Lorm\queries\maker\exceptions;

/**
 * mysql on exception
 */
class OnException extends QueryException
{
  public $on = null;

  public function __construct($description, $on = null)
  {
    parent::__construct($description);
    $this->on = $on;
  }
}
