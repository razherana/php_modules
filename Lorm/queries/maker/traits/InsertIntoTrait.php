<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\InsertInto;

trait InsertIntoTrait
{
  use RequestTrait;

  /**
   * @param string $table_name
   * @param string[] $values
   */
  public static function insert_into($table_name, $values)
  {
    $a = new static();
    $a->elements[] = new InsertInto($table_name, $values);
    return $a;
  }
}
