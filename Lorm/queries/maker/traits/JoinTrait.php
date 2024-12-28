<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\Join;
use Lorm\queries\maker\request\Queryable;

trait JoinTrait
{
  use RequestTrait;

  /**
   * @param string|Queryable $table_or_query
   * @param ?string $as
   * @param int $type
   */
  public function join($table_or_query, $as = null, $type = Join::NONE)
  {
    $this->elements[] = new Join($table_or_query, $as, $type);
    return $this;
  }
}
