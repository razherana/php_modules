<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\request\interfaces\Element;

class Order implements Element
{
  private const TYPE = ["DESC", "ASC"];

  public const DESC = 0, ASC = 1;

  private $data = [];

  public function decode(): string
  {
    return "ORDER BY " . implode(' ', $this->data);
  }

  public function __construct($type, $column)
  {
    $this->data = [$column, self::TYPE[$type]];
  }
}
