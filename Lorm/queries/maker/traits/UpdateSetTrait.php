<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\UpdateSet;

trait UpdateSetTrait
{
  use RequestTrait;

  public static function update_set($table_name, $set_values)
  {
    $a = new static;
    $a->elements[] = new UpdateSet($table_name, $set_values);
    return $a;
  }
}
