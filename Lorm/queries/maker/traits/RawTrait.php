<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\Raw;

trait RawTrait
{
  use RequestTrait;

  /**
   * @param string $content
   */
  protected function raw_instance($content)
  {
    $this->elements[] = new Raw($content);
    return $this;
  }

  /**
   * @param string $content
   */
  protected static function raw_static($content)
  {
    $a = new static();
    return $a->raw_instance($content);
  }
}
