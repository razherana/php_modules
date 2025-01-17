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

  abstract public function addAll($elements): bool;
  abstract public function add($element): bool;
  abstract public function remove($element): bool;
  abstract public function contains($element): bool;
  abstract public function size(): int;
  abstract public function isEmpty(): bool;
  abstract public function toArray(): array;
  abstract public function clear(): void;
  abstract public function iterator(): \Iterator;
}
