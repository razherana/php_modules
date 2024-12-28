<?php

namespace Lorm\queries\maker\traits;

use Lorm\queries\maker\exceptions\WhereException;
use Lorm\queries\maker\queries\DefaultQueryMaker;
use Lorm\queries\maker\request\elements\Where;

/**
 * Represents a trait which contains 'where' functions
 * Only works in a Queryable element
 * @method static static where(mixed $element1, string $condition, mixed $element2, bool $clean = true)
 * @method static where(mixed $element1, string $condition, mixed $element2, bool $clean = true)
 */
trait WhereTrait
{
  use RequestTrait;

  /**
   * Defines a new static with a where
   * @param string $condition
   */
  protected static function where_static($element1, $condition, $element2, $clean = true)
  {
    $el = new static();
    return $el->where_instance($element1, $condition, $element2, $clean);
  }

  protected function where_instance($element1, $condition, $element2, $clean = true)
  {
    $this->elements[] = new Where($element1, $condition, $element2, Where::NONE, $clean);
    return $this;
  }

  public function and_where($element1, $condition, $element2, $clean = true)
  {
    $this->elements[] = new Where($element1, $condition, $element2, Where::AND, $clean);
    return $this;
  }

  public function or_where($element1, $condition, $element2, $clean = true)
  {
    $this->elements[] = new Where($element1, $condition, $element2, Where::OR, $clean);
    return $this;
  }

  /**
   * Why public ?
   * Because you can only call this in an instance not static
   * @param \Closure $conditions
   */
  public function or_group_where($conditions)
  {
    $dummy = new DefaultQueryMaker;
    $dummy->mode_test = true;
    $conditions = $conditions->bindTo($dummy, static::class);
    $conditions();

    $elements = $dummy->elements;
    $this->elements[] = ['type' => Where::class, Where::OR, $elements];

    return $this;
  }

  /**
   * Why public ?
   * Because you can only call this in an instance not static
   * @param \Closure $conditions
   */
  public function and_group_where($conditions)
  {
    $dummy = new DefaultQueryMaker;
    $dummy->mode_test = true;
    $conditions = $conditions->bindTo($dummy, static::class);
    $conditions();

    $elements = $dummy->elements;
    $this->elements[] = [
      'type' => Where::class,
      'type_where' => Where::AND,
      'elements' => $elements
    ];

    return $this;
  }

  /**
   * Adds where, then typegiven where next
   * Uses = for the operator, if a third element exists
   * It will be used as the operator
   * @param array<int, array<int, string>> $wheres
   * @param int $type_to_use
   * @param string $default_operator
   */
  public function where_all($wheres, $type_to_use = Where::AND, $default_operator = "=")
  {
    if (!is_array($wheres)) {
      throw new WhereException("The \$wheres in where_all() is not an array");
    }

    switch ($type_to_use) {
      case Where::AND:
        $type_to_use = "and";
        break;
      case Where::OR:
        $type_to_use = "or";
        break;
      default:
        throw new WhereException("The type to use is undefined: $type_to_use");
    }

    $wheres = array_values($wheres);

    if (count($wheres) === 0) return $this;

    $this->where_instance(
      $wheres[0][0],
      $wheres[0][2] ?? $default_operator,
      $wheres[0][1]
    );

    if (count($wheres) === 1)
      return $this;
    else unset($wheres[0]);

    foreach ($wheres as $where) {
      $this->{$type_to_use . "_where"}(
        $where[0],
        $where[2] ?? $default_operator,
        $where[1]
      );
    }

    return $this;
  }
}
