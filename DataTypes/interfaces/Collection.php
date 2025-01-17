<?php

namespace DataTypes\interfaces;

use ArrayAccess;
use DataTypes\utils\ElementChecker;
use JsonSerializable;

abstract class Collection implements ArrayAccess, JsonSerializable
{
  use ElementChecker;

  public function jsonSerialize(): mixed
  {
    return $this->toArray();
  }

  /**
   * This method appends all of the elements in the specified list to the end of this list, in the order that they are returned by the specified list's Iterator.
   * @param $elements
   * @return bool
   */
  abstract public function addAll($elements): bool;

  /**
   * This method inserts the specified element in this collection.
   * @param $element
   * @return bool
   */
  abstract public function add($element): bool;

  /**
   * This method removes the first occurrence of the specified element from this collection, if it is present.
   * @param $element
   * @return bool
   */
  abstract public function remove($element): bool;

  /**
   * This method tells whether this collection contains the specified element.
   * @param $element
   * @return bool
   */
  abstract public function contains($element): bool;

  /**
   * This method gives the number of elements in this collection.
   * @return int
   */
  abstract public function size(): int;

  /**
   * This method tells whether this collection is empty.
   * @return bool
   */
  abstract public function isEmpty(): bool;

  /**
   * This method returns the elements in this collection as an array.
   * @return array
   */
  abstract public function toArray(): array;

  /**
   * This method removes all of the elements from this collection.
   * @return void
   */
  abstract public function clear(): void;

  /**
   * This method returns an iterator over the elements in this collection.
   * @return \Iterator
   */
  abstract public function iterator(): \Iterator;
}
