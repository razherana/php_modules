<?php

namespace Lorm\queries\maker\exceptions;

use Lorm\queries\maker\request\Queryable;

/**
 * Exception throwing when decoding a query
 */
class QueryDecodingException extends QueryException
{
  /**
   * Contains the query
   * @var Queryable $query
   */
  public $query = null;

  public function __construct($description, $query = null)
  {
    parent::__construct($description);
    $this->query = $query;
  }
}
