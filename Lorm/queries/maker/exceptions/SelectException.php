<?php

namespace Lorm\queries\maker\exceptions;

use Lorm\queries\maker\request\elements\Select;

class SelectException extends QueryException
{
  /**
   * @var ?Select $select
   */
  public $select;

  public function __construct($message, $select = null)
  {
    parent::__construct($message);
    $this->select = $select;
  }
}
