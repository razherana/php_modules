<?php

namespace CheckInput;

use ArrayAccess;

class ArrayDataAccessor implements ArrayAccess
{
  private $access_method;
  private $data;

  public function __construct($data, $access_method)
  {
    $this->data = $data;
    $this->access_method = $access_method;
  }

  public function offsetExists($offset): bool
  {
    return true;
  }

  public function offsetGet($offset): mixed
  {
    return @($this->access_method)($offset, $this->data);
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    // Do nothing
  }

  public function offsetUnset(mixed $offset): void
  {
    // Do nothing
  }
}
