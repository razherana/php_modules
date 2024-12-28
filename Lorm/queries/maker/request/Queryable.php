<?php

namespace Lorm\queries\maker\request;

use BadMethodCallException;
use ReflectionClass;

use Lorm\queries\maker\exceptions\InsertIntoException;
use Lorm\queries\maker\exceptions\GroupableElementException;

use Lorm\queries\maker\request\elements\InsertInto;

use Lorm\queries\maker\request\interfaces\Element;
use Lorm\queries\maker\request\interfaces\GroupableElement;

use Lorm\queries\maker\traits\RequestTrait;

/**
 * Represents an abstract class who can form mysql queries
 */
abstract class Queryable
{
  /**
   * Contains the elements of the query
   * in order
   * @var Element[] $elements
   */
  public $elements = [];

  /**
   * Mode test or value getter
   * Set to true if to use on some mysql element 
   * @var bool $mode_test
   */
  public $mode_test = false;

  final protected function verify_query()
  {
    // If mode test, then check nothing
    if ($this->mode_test) return;

    // Verify if not a insert into query and followed by other things
    // Note: InsertInto query has only one element -> the InsertInto instance itself
    if (!empty($this->elements) && $this->elements[0] instanceof InsertInto && count($this->elements) > 1) {
      $other_query_types = $this->elements;
      unset($other_query_types[0]);

      foreach ($other_query_types as $k => $o) {
        $other_query_types[$k] = $o::class;
      }

      throw new InsertIntoException("InsertInto query is followed by other query types (" . implode(', ', $other_query_types) . ')');
    }

    // Verify if there is an insert into in the middle of query
    foreach (array_values($this->elements) as $k => $e) if ($e instanceof InsertInto && $k != 0)
      throw new InsertIntoException("InsertInto mysql query element in the middle of the query (Element num : " . ($k + 1) . ")");
  }

  /**
   * Decode group of query elements
   * @param array $array_query
   */
  final protected static function decode_array_query($array_query): string
  {
    // Verification
    if (!isset($array_query['type'])) {
      throw new GroupableElementException("This Queryable contains a random array");
    }
    // Checks the type if it is a class who implements Element 
    else if (!in_array(Element::class, (new ReflectionClass($array_query['type']))->getInterfaceNames())) {
      throw new GroupableElementException("This Queryable contains a group with an unknown type (" . $array_query['type'] . ")");
    }
    // Checks the type if it is a class who implements GroupableElement 
    else if (!in_array(GroupableElement::class, (new ReflectionClass($array_query['type']))->getInterfaceNames())) {
      throw new GroupableElementException("This Element contains a group but doesn't have a group_decoder");
    }

    return $array_query['type']::decode_group($array_query);
  }

  /**
   * Use these traits for __call and __callStatic
   * @return \ReflectionClass[]
   */
  protected static function use_traits(): array
  {
    return (new ReflectionClass(static::class))->getTraits();
  }

  public function __call($name, $arguments): static
  {
    // Checks for all traits if one of the trait has the method
    foreach (static::use_traits() as $name1 => $trait)
      // Checks the trait if it uses has RequestTrait
      if (in_array(RequestTrait::class, $trait->getTraitNames())) {
        // Checks the trait if it has the method
        if (method_exists($name1, $name . '_instance'))
          return $this->{$name . '_instance'}(...$arguments);
      }
    throw new BadMethodCallException("Undefined method " . $name);
  }

  public static function __callStatic($name, $arguments): static
  {
    // Checks for all traits if one of the trait has the method
    foreach (static::use_traits() as $name1 => $trait) {
      // Checks the trait if it uses has RequestTrait
      if (in_array(RequestTrait::class, $trait->getTraitNames())) {
        // Checks the trait if it has the method
        if (method_exists($name1, $name . '_static'))
          return static::{$name . '_static'}(...$arguments);
      }
    }
    throw new BadMethodCallException("Undefined static method " . $name);
  }

  /**
   * Search an element inside $this->elements
   * This method ignores group element
   * 
   * @param string[]|string $type The types of the element searched
   * @param ?\Closure $callable If the callable is defined, 
   * it uses that callable as a second comparator. The callable is binded with the element in question
   */
  final public function search_element($type, $callable = null): Element|false
  {
    if (is_string($type)) $type = [$type];

    if (is_null($callable)) $callable = function () {
      return true;
    };

    foreach ($this->elements as $e) {
      if (is_array($e)) continue;

      if (in_array($e::class, $type) && $callable->call($e)) {
        return $e;
      }
    }

    return false;
  }

  /**
   * Pushes the content of $query inside of the current query
   * @param static $query
   */
  public function push_query($query)
  {
    foreach ($query->elements as $e)
      $this->elements[] = $e;
    return $this;
  }

  /**
   * Decodes the query
   * @return string
   */
  public abstract function decode_query(): string;
}
