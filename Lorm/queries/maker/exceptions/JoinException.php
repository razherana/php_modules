<?php

namespace Lorm\queries\maker\exceptions;

/**
 * mysql Join exception
 */
class JoinException extends QueryException
{
  public $join = null;

  public function __construct($description, $join = null)
  {
    parent::__construct($description);
    $this->join = $join;
  }
}
