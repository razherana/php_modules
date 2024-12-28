<?php

namespace Lorm\queries\maker\exceptions;

/**
 * Default exception type for orm\msql 
 */
class QueryException extends \Exception
{
  /**
   * @param string $description
   */
  public function __construct($description)
  {
    parent::__construct($description, 1);
  }
}
