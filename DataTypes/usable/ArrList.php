<?php

namespace DataTypes\usable;

use DataTypes\interfaces\ListInterface;
use DataTypes\utils\Types;

class ArrList extends ListInterface
{
  public function addAll($elements): bool
  {
    if (!($elements instanceof ArrList))
      return false;
    if (!$elements->getTypes()[0]->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . $elements->getTypes()[0] . " !== " . $this->getTypes()[0]);
    foreach ($elements->toArray() as $element)
      $this->add($element);
  }

  /**
   * Inserts the specified element at the specified position in this list.
   * @param $index
   * @param $element
   * @return bool
   */
  public function addAt($index, $element): bool
  {
    if ($this->offsetExists($index))
      throw new \OutOfBoundsException("Index out of bounds : $index < 0 or $index > " . $this->size());
    if (!Types::getType($element)->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . Types::getType($element) . " !== " . $this->getTypes()[0]);
    array_splice($this->elements, $index, 0, [$element]);
    return true;
  }

  public function indexOf($element): int
  {
    $index = array_search($element, $this->elements);
    return $index === false ? -1 : $index;
  }

  public function lastIndexOf($element): int
  {
    $index = array_search($element, array_reverse($this->elements, true));
    return $index === false ? -1 : $index;
  }

  public function removeAt($index): bool
  {
    if (!$this->offsetExists($index))
      return false;
    array_splice($this->elements, $index, 1);
    $this->elements = array_values($this->elements);
    return true;
  }

  public function set($index, $element): bool
  {
    if (!$this->offsetExists($index))
      return false;
    $this->elements[$index] = $element;
    return true;
  }

  public function subList($fromIndex, $toIndex): ListInterface
  {
    $arrList = new ArrList($this->getTypes()[0]);

    if ($fromIndex < 0 || $toIndex > $this->size() || $fromIndex > $toIndex)
      throw new \OutOfBoundsException("Index out of bounds : $fromIndex < 0 or $toIndex > " . $this->size() . " or $fromIndex > $toIndex");

    $arrList->addAll(array_slice($this->elements, $fromIndex, $toIndex - $fromIndex));
    return $arrList;
  }

  public function contains($element): bool
  {
    return in_array($element, $this->elements);
  }

  public function isEmpty(): bool
  {
    return empty($this->elements);
  }

  public function toArray(): array
  {
    return $this->elements;
  }

  public function clear(): void
  {
    $this->elements = [];
  }

  private $elements = [];

  public function __construct($type)
  {
    $this->setTypes([$type]);
  }

  public function add($element): bool
  {
    if (!Types::getType($element)->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . Types::getType($element) . " !== " . $this->getTypes()[0]);
    $this->elements[] = $element;
    return true;
  }

  private function remove0($index)
  {
    unset($this->elements[$index]);
  }

  public function remove($element): bool
  {
    $index = $this->indexOf($element);

    if ($index === -1)
      return false;

    $this->remove0($index);
    $this->elements = array_values($this->elements);
    return true;
  }

  public function get($index)
  {
    if (!$this->offsetExists($index))
      throw new \OutOfBoundsException("Index out of bounds : $index < 0 or $index >= " . $this->size());
    return $this->elements[$index];
  }

  public function size(): int
  {
    return count($this->elements);
  }

  // Utils methods start here

  public function map($closure): ArrList
  {
    $arrList = new ArrList($this->getTypes()[0]);
    foreach ($this->elements as $element)
      $arrList->add($closure($element));
    return $arrList;
  }

  public function filter($closure): ArrList
  {
    $arrList = new ArrList($this->getTypes()[0]);
    foreach ($this->elements as $element)
      if ($closure($element))
        $arrList->add($element);
    return $arrList;
  }

  public function reduce($closure, $initialValue)
  {
    $accumulator = $initialValue;
    foreach ($this->elements as $element)
      $accumulator = $closure($accumulator, $element);
    return $accumulator;
  }

  public function forEach($closure): void
  {
    foreach ($this->elements as $element)
      $closure($element);
  }

  public function sort($closure): void
  {
    usort($this->elements, $closure);
  }

  public function containsAll($elements): bool
  {
    if (!($elements instanceof ArrList))
      return false;
    if (!$elements->getTypes()[0]->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . $elements->getTypes()[0] . " !== " . $this->getTypes()[0]);
    foreach ($elements->toArray() as $element)
      if (!$this->contains($element))
        return false;
    return true;
  }

  public function equals($arrList): bool
  {
    if (!($arrList instanceof ArrList))
      return false;
    if ($arrList->size() !== $this->size())
      return false;
    for ($i = 0; $i < $this->size(); $i++)
      if ($arrList->get($i) !== $this->get($i))
        return false;
    return true;
  }

  public function __toString()
  {
    return json_encode($this->elements);
  }

  public function removeIf($closure): bool
  {
    $removed = false;
    foreach ($this->elements as $index => $element)
      if ($closure($element)) {
        unset($this->elements[$index]);
        $removed = true;
      }
    return $removed;
  }

  /**
   * This method retains all elements in this list that are present in the specified list.
   * @param $elements
   */
  public function retainAll($elements): bool
  {
    if (!($elements instanceof ArrList))
      return false;
    if (!$elements->getTypes()[0]->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . $elements->getTypes()[0] . " !== " . $this->getTypes()[0]);
    $retained = false;
    foreach ($this->elements as $index => $element)
      if (!$elements->contains($element)) {
        $this->remove0($index);
        $retained = true;
      }
    $this->elements = array_values($this->elements);
    return $retained;
  }

  public function removeAll($elements): bool
  {
    if (!($elements instanceof ArrList))
      return false;
    if (!$elements->getTypes()[0]->equals($this->getTypes()[0]))
      throw new \InvalidArgumentException("Type mismatch : " . $elements->getTypes()[0] . " !== " . $this->getTypes()[0]);
    $removed = false;
    foreach ($elements->toArray() as $element)
      if ($this->contains($element)) {
        $this->remove($element);
        $removed = true;
      }
    return $removed;
  }

  public function __clone()
  {
    $arrList = new ArrList($this->getTypes()[0]);
    foreach ($this->elements as $element)
      $arrList->add($element);
    return $arrList;
  }
}
