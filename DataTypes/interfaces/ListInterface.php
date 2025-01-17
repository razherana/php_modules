<?php

namespace DataTypes\interfaces;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

abstract class ListInterface extends Collection implements IteratorAggregate
{
  abstract public function addAt($element, int $index): bool;
  abstract public function get(int $index);
  abstract public function indexOf($element): int;
  abstract public function lastIndexOf($element): int;
  abstract public function removeAt(int $index): bool;
  abstract public function set($element, int $index): bool;
  abstract public function subList(int $fromIndex, int $toIndex): ListInterface;

  public function offsetExists(mixed $offset): bool
  {
    return is_int($offset) && $offset >= 0 && $offset < $this->size();
  }

  public function offsetGet(mixed $offset): mixed
  {
    if (!$this->offsetExists($offset))
      throw new \OutOfBoundsException("Index out of bounds : $offset < 0 or $offset >= " . $this->size());
    return $this->get($offset);
  }

  public function offsetSet(mixed $offset, mixed $value): void
  {
    if ($offset === null)
      $this->add($value);
    else
      $this->set($value, $offset);
  }

  public function offsetUnset(mixed $offset): void
  {
    if (!$this->offsetExists($offset))
      throw new \OutOfBoundsException("Index out of bounds : $offset < 0 or $offset >= " . $this->size());
    $this->removeAt($offset);
  }

  public function getIterator(): Traversable
  {
    return new ArrayIterator($this->toArray());
  }

  public function iterator(): \ArrayIterator
  {
    return new \ArrayIterator($this->toArray());
  }
}
