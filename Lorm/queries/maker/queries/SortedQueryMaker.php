<?php

namespace Lorm\queries\maker\queries;

use Lorm\queries\maker\exceptions\JoinException;
use Lorm\queries\maker\exceptions\OnException;
use Lorm\queries\maker\exceptions\QueryDecodingException;
use Lorm\queries\maker\request\elements\Delete;
use Lorm\queries\maker\request\elements\From;
use Lorm\queries\maker\request\elements\InsertInto;
use Lorm\queries\maker\request\elements\Join;
use Lorm\queries\maker\request\elements\On;
use Lorm\queries\maker\request\elements\Order;
use Lorm\queries\maker\request\elements\Select;
use Lorm\queries\maker\request\elements\UpdateSet;
use Lorm\queries\maker\request\elements\Where;
use Lorm\queries\maker\traits\DeleteTrait;
use Lorm\queries\maker\traits\FromTrait;
use Lorm\queries\maker\traits\InsertIntoTrait;
use Lorm\queries\maker\traits\JoinTrait;
use Lorm\queries\maker\traits\OnTrait;
use Lorm\queries\maker\traits\OrderTrait;
use Lorm\queries\maker\traits\SelectTrait;
use Lorm\queries\maker\traits\UpdateSetTrait;
use Lorm\queries\maker\traits\WhereTrait;

/**
 * Like DefaultQueryMaker but the mysql elements are sorted
 */
class SortedQueryMaker extends DefaultQueryMaker
{
  use SelectTrait, WhereTrait, FromTrait, OrderTrait, DeleteTrait, InsertIntoTrait, OnTrait, JoinTrait, UpdateSetTrait;

  /**
   * Contains the unordered $elements
   * @var ?array $old_elements 
   */
  private $old_elements = null;

  public function decode_query(): string
  {
    $this->old_elements = $this->elements;
    $this->elements = $this->sort_query();
    return parent::decode_query();
  }

  private function sort_query()
  {
    $wheres = [];
    $order_bys = [];

    /**
     * @var array<array<Join, On>>
     */
    $join_ons = [];

    $select = null;
    $delete = null;
    $insert_into = null;
    $from = null;
    $update_set = null;

    foreach ($this->elements as $e) {
      if (is_array($e)) switch ($e['type']) {
        case Where::class:
          $wheres[] = $e;
          break;
      }
      else switch ($e::class) {
        case Where::class:
          $wheres[] = $e;
          break;
        case Order::class:
          $order_bys[] = $e;
          break;
        case Join::class:
          $join_ons[] = [$e];
          break;
        case On::class:
          $join_ons[array_key_last($join_ons)][] = $e;
          break;
        case Select::class:
          $select = $e;
          break;
        case Delete::class:
          $delete = $e;
          break;
        case InsertInto::class:
          $insert_into = $e;
          break;
        case From::class:
          $from = $e;
          break;
        case UpdateSet::class:
          $update_set = $e;
          break;
      }
    }

    // Separate Join and On inside the same array
    $joins_divised = [];
    foreach ($join_ons as $j) {

      if (count($j) != 2) {
        if ($j[array_key_first($j)] instanceof Join)
          throw new JoinException("This join has no On conditions", $j[array_key_first($j)]);

        throw new OnException("This on condition has no Join mysql element", $j[array_key_first($j)]);
      }

      $joins_divised[] = $j[0];
      $joins_divised[] = $j[1];
    }

    // $select, $delete, $insert_into, $update_set
    // Only ONE should be NOT null
    $count = count($arr = array_filter([$select, $delete, $insert_into, $update_set], function ($e) {
      return $e !== null;
    }));

    // Gets the classname inside $arr (array_containing only the $main)
    $class_names = [];
    if ($count > 0) foreach ($arr as $v) $class_names[] = $v::class;

    if ($count > 1)
      throw new QueryDecodingException("This query has more than one main : [" . implode(' ,', $class_names) . "]", $this);
    if ($count <= 0)
      throw new QueryDecodingException("This query has no main", $this);

    $main = $arr[array_key_first($arr)];

    if ($insert_into !== null)
      return array($insert_into);
    else if ($update_set !== null)
      return array($update_set, ...$wheres);
    else
      return array($main, $from, ...$joins_divised, ...$wheres, ...$order_bys);
  }
}
