<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\On;

trait OnTrait
{
  use RequestTrait;

  /**
   * @param \Closure $condition_callable
   */
  public function on($condition_callable)
  {
    $this->elements[] = new On($condition_callable, static::class);
    return $this;
  }
}
