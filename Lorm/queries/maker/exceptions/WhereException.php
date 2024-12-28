<?php

namespace Lorm\queries\maker\exceptions;

class WhereException extends QueryException
{
  /**
   * Constructs a  where exception
   */
  public function __construct($description)
  {
    parent::__construct($description);
  }
}
