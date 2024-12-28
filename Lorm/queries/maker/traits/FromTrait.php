<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\From;
use Lorm\queries\maker\request\Queryable;

trait FromTrait
{
  use RequestTrait;

  /**
   * @param string|Queryable $element
   */
  public function from($element, $as = null)
  {
    $this->elements[] = new From($element, $as);
    return $this;
  }
}
