<?php

namespace Lorm;

use Lorm\queries\QueryExecutor;

use Closure;
use Exception;
use Lorm\queries\QueryUtil;

abstract class BaseModel
{
  /**
   * The primary key column
   * @var string
   */
  protected $primary_key = "";

  /**
   * The model's table name
   * @var string
   */
  protected $table = "";

  /**
   * The model's column
   * @var string[]
   */
  protected $columns = [];

  /**
   * All of the relations to pre-load
   * @var string[]
   */
  protected $eager_load = [];

  /**
   * Default value if the column is not on the received table
   * @var mixed
   */
  protected $default = null;

  /**
   * All of casts before using elements
   * @var array<string,\Closure>
   */
  protected function get_cast(): array
  {
    return [];
  }

  /**
   * All of casts before using in a query
   * @var array<string,\Closure>
   */
  protected function set_cast(): array
  {
    return [];
  }

  /**
   * All of casts before using elements
   * @var array<string,\Closure>
   */
  private $get_cast = null;

  /**
   * All of casts before using in a query
   * @var array<string,\Closure>
   */
  private $set_cast = null;

  private $data = [];
  private $og_data = [];
  private $joins = [];

  /**
   * @param string $otherModel
   * @param Closure $closureJoin
   * @param string $relation_name
   */
  private function hasManyMethod($otherModel, $closureJoin, $relation_name)
  {
    $others = $otherModel::all();
    if (!isset($mainModel->joins[$relation_name]))
      $this->joins[$relation_name] = [];
    foreach ($others as $other)
      if ($closureJoin($this, $other))
        $this->joins[$relation_name][] = $other;
  }

  public function hasMany($otherModel, $closureJoin, $relation_name)
  {
    return ["hasMany", $otherModel, $closureJoin, $relation_name];
  }

  public function belongsTo($otherModel, $closureJoin, $relation_name)
  {
    return ["belongsTo", $otherModel, $closureJoin, $relation_name];
  }


  /**
   * @param BaseModel[] $models
   * @param string $otherModel
   * @param Closure $closureJoin
   * @param string $relation_name
   */
  private static function hasManyStatic($models, $otherModel, $closureJoin, $relation_name)
  {
    $others = $otherModel::all();
    foreach ($models as $currModel) {
      if (!isset($mainModel->joins[$relation_name]))
        $currModel->joins[$relation_name] = [];
      foreach ($others as $other)
        if ($closureJoin($currModel, $other))
          $currModel->joins[$relation_name][] = $other;
    }
  }

  /**
   * @param string $otherModel
   * @param Closure $closureJoin
   * @param string $relation_name
   */
  private function belongsToMethod($otherModel, $closureJoin, $relation_name)
  {
    $others = $otherModel::all();
    foreach ($others as $other)
      if ($closureJoin($this, $other)) {
        $this->joins[$relation_name] = $other;
        break;
      }
  }

  /**
   * @param BaseModel[] $models
   * @param string $otherModel
   * @param Closure $closureJoin
   * @param string $relation_name
   */
  private static function belongsToStatic($models, $otherModel, $closureJoin, $relation_name)
  {
    $others = $otherModel::all();
    foreach ($models as $currModel) {
      foreach ($others as $other) {
        if ($closureJoin($currModel, $other)) {
          $currModel->joins[$relation_name] = $other;
          break;
        }
      }
    }
  }

  private static function applyAllEagerLoad($models, $options)
  {
    $example = new static();
    foreach ($options as $k => $v)
      $example->{$k} = $v;
    $eager_load = $example->eager_load;
    foreach ($eager_load as $e) {
      $rel = $example->{$e}();
      call_user_func_array([self::class, $rel[0] . "Static"], array_merge([$models], array_slice($rel, 1)));
    }
  }

  public function __construct($data = null)
  {
    if ($data !== null && is_array($data))
      $this->init($data);
  }

  /**
   * @param array<string, mixed> $data
   */
  private function init($data)
  {
    $data = array_filter($data, function ($k) {
      return in_array($k, $this->columns);
    }, ARRAY_FILTER_USE_KEY);

    $keys = array_keys($data);
    foreach ($this->columns as $col)
      if (!in_array($col, $keys))
        $data[$col] = $this->default;

    if ($this->get_cast === null)
      $this->get_cast = $this->get_cast();

    foreach ($this->get_cast as $col => $cast)
      $data[$col] = $cast($data[$col]);

    $this->data = $data;
    $this->og_data = $data;
  }

  private function get_data()
  {
    $data = $this->data;

    if ($this->set_cast === null)
      $this->set_cast = $this->set_cast();

    foreach ($this->set_cast as $col => $cast)
      $data[$col] = $cast($data[$col]);

    return $data;
  }

  private function get_og_data()
  {
    $data = $this->og_data;

    if ($this->set_cast === null)
      $this->set_cast = $this->set_cast();

    foreach ($this->set_cast as $col => $cast)
      $data[$col] = $cast($data[$col]);

    return $data;
  }

  public function __get($name)
  {
    if (isset($this->joins[$name])) {
      return $this->joins[$name];
    } else if (method_exists($this, $name)) {
      $rel = $this->{$name}();
      call_user_func([$this, $rel[0] . "Method"], ...array_slice($rel, 1));
      return $this->joins[$name];
    }

    if (in_array($name, $this->columns))
      return $this->data[$name] ?? null;
    else
      throw new Exception("No column $name");
  }

  public function __set($name, $value)
  {
    if (in_array($name, $this->columns))
      $this->data[$name] = $value;
    else
      throw new Exception("No column $name");
  }

  public static function all($options = [])
  {
    $arr = [];
    $example = new static();
    $q = "SELECT * FROM " . $example->table;
    $res = QueryExecutor::execute($q);
    foreach ($res as $line) {
      $el = new static();
      foreach ($options as $k => $v)
        $el->{$k} = $v;
      $el->init($line);
      $arr[] = $el;
    }
    self::applyAllEagerLoad($arr, $options);
    return $arr;
  }

  public function insert()
  {
    $data = $this->get_data();
    $columns = array_filter($this->columns, fn($e) => $e != $this->primary_key);
    $params = [];
    foreach ($columns as $col) {
      $value = $data[$col] ?? null;
      $params[":$col"] = $value;
    }
    $q = "INSERT INTO " . $this->table . " (" . implode(", ", $columns) . ") VALUES (" . implode(", ", array_keys($params)) . ")";
    QueryExecutor::execute($q, $params);
  }

  public function update($use_pk = true)
  {
    $data = $this->get_data();
    $og_data = $this->get_og_data();
    $table = $this->table;
    $columns = $this->columns;
    $primary_key = $this->primary_key;

    $ser = [];
    foreach ($data as $k => $v)
      $ser[":col_$k"] = $v;

    $ser_og = [];
    foreach ($data as $k => $v)
      $ser_og[":$k"] = $v;

    $q = "UPDATE $table SET " . implode(", ", array_map(fn($e) => $e . " = :col_$e", $columns)) . " WHERE ";
    if (($og_pk = (!empty($primary_key) && $use_pk === true)) || ($new_pk = is_string($use_pk))) {
      $pk = $og_pk === true && ($new_pk ?? false) === false ? $primary_key : $use_pk;

      $where_pk = QueryUtil::where_parameter($pk, $og_data[$pk]);
      $q .= $where_pk;

      return QueryExecutor::execute($q, $ser + [":$pk" => $og_data[$pk]]);
    }

    $where_data = [];
    foreach ($data as $k => $e)
      $where_data[] = [$k, $e];

    $q .= QueryUtil::where_parameter_all($where_data);

    return QueryExecutor::execute($q, $ser + $ser_og);
  }
}
