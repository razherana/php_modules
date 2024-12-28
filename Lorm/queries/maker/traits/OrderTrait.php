<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\request\elements\Order;

trait OrderTrait
{
  use RequestTrait;

  /**
   * @param string $column
   */
  public function order_by_desc($column)
  {
    $this->elements[] = new Order(Order::DESC, $column);
    return $this;
  }

  /**
   * @param string $column
   */
  public function order_by_asc($column)
  {
    $this->elements[] = new Order(Order::ASC, $column);
    return $this;
  }
}
