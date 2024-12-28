<?php

namespace Lorm\queries\maker\queries;

use Lorm\queries\maker\request\Queryable;
use Lorm\queries\maker\traits\DeleteTrait;
use Lorm\queries\maker\traits\FromTrait;
use Lorm\queries\maker\traits\InsertIntoTrait;
use Lorm\queries\maker\traits\JoinTrait;
use Lorm\queries\maker\traits\OnTrait;
use Lorm\queries\maker\traits\OrderTrait;
use Lorm\queries\maker\traits\RawTrait;
use Lorm\queries\maker\traits\SelectTrait;
use Lorm\queries\maker\traits\UpdateSetTrait;
use Lorm\queries\maker\traits\WhereTrait;

/**
 * This is a default query maker who just decodes everything in order without re-arranging
 */
class DefaultQueryMaker extends Queryable
{
  use SelectTrait, WhereTrait, FromTrait, OrderTrait, RawTrait, DeleteTrait, InsertIntoTrait, OnTrait, JoinTrait, UpdateSetTrait;

  /**
   * Contains the temporary query
   * @var string $temp_query
   */
  protected $temp_query = null;

  final public function reset()
  {
    $this->temp_query = null;
  }

  public function decode_query(): string
  {
    if (!is_null($this->temp_query)) return $this->temp_query;

    $this->verify_query();
    $els = [];
    foreach ($this->elements as $e) {
      if (is_array($e))
        $els[] = self::decode_array_query($e);
      else
        $els[] = $e->decode();
    }
    return implode(' ', $els);
  }
}
