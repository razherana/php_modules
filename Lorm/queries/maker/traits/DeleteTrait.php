<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\Delete;

trait DeleteTrait
{
  use RequestTrait;

  public static function delete()
  {
    $a = new static;
    $a->elements[] = new Delete;
    return $a;
  }
}
