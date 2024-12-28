<?php

namespace Lorm;

use Lorm\queries\maker\queries\SortedQueryMaker;
use Lorm\queries\maker\traits\FromTrait;
use Lorm\queries\maker\traits\OrderTrait;
use Lorm\queries\maker\traits\SelectTrait;
use Lorm\queries\maker\traits\WhereTrait;

class ModelQuery extends SortedQueryMaker
{
  use WhereTrait, OrderTrait, SelectTrait, FromTrait;

  /** @var BaseModel */
  protected $model;

  /**
   * @param BaseModel $model
   */
  public function __construct($model)
  {
    $this->model = $model;
    $this->select(["*"])->from($this->model->table);
  }

  /**
   * Add eager_loads
   */
  public function preload($array = [])
  {
    $this->model->eager_load = array_values(array_unique(array_merge($this->model->eager_load, $array)));
    return $this;
  }

  /**
   * Returns the result of the query
   */
  public function get()
  {
    $q = $this->decode_query();
    return $this->model::query($q, ["eager_load" => $this->model->eager_load]);
  }
}
