<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\exceptions\SelectException;
use Lorm\BaseModel;
use Lorm\queries\maker\request\interfaces\Element;

class Select implements Element
{
  /**
   * Contains the select elements
   */
  protected $select = [];

  /**
   * Contains old selects
   */
  protected $old_select = [];

  public function old_select()
  {
    return $this->old_select;
  }

  /**
   * @param array<int|string, string|array<string, string>>|string $elements
   */
  public function __construct($elements = ['*'], $use_model_prefix = true)
  {
    if (is_string($elements)) {
      if (!is_a($elements, BaseModel::class, true)) {
        throw new SelectException("The string given to the select is not a BaseModel::class but a : " . $elements, $this);
      }

      $elements = self::select_from_model($elements, $use_model_prefix);
    } elseif (
      // Checks the elements if it is an array
      is_array($elements)
      // Checks that it has one element
      && count($elements) == 1
      // Checks that the first key is a string (so it can be an alias)
      && is_string($key = array_key_first($elements))
      // Checks that the element is a basemodel classname
      && is_a($elements[$key], BaseModel::class, true)
    ) {
      $select_other = new self($elements[$key], $use_model_prefix);
      $select_other->prefix_everything($key);

      $this->old_select = $select_other->old_select;
      $this->select = $select_other->select;
      return;
    }

    if ($elements instanceof Select) {
      $this->old_select = $elements->old_select;
      $this->select = self::clean_elements($this->old_select);
      return;
    }

    $this->old_select = $elements;
    $this->select = self::clean_elements($elements);
  }

  /**
   * @param string $model_name The model's name
   */
  protected static function select_from_model($model_name, $use_model_prefix = true)
  {
    /**
     * @var BaseModel $model
     */
    $model = new $model_name;
    $columns = $model->columns;

    $all = [];

    $prefix = '';

    if (is_string($use_model_prefix)) {
      $prefix = $use_model_prefix . ".";
    } elseif ($use_model_prefix === true) {
      $prefix = $model->table . ".";
    }

    foreach ($columns as $col)
      $all[] = $prefix . $col;

    return $all;
  }

  /**
   * @param array $arr
   * @param array ...$arrays
   */
  public static function merge_and_keep_keys_if_string($arr, ...$arrays): array
  {
    foreach ($arrays as $arr2) foreach ($arr2 as $k => $v) {
      if (!is_string($k)) {
        $arr[] = $v;
        continue;
      }

      if (!isset($arr[$k])) {
        $arr[$k] = $v;
        continue;
      }

      if (!is_array($arr[$k]))
        $arr[$k] = [$arr[$k]];

      $arr[$k][] = $v;
    }

    return $arr;
  }

  /**
   * @param array<int|string, string>|self $elements
   */
  public function add_select($elements, $use_model_name_prefix = true): self
  {
    if (is_a($elements, static::class)) {
      $temp = $elements;
    } else {
      // TOADD Array functionality
      $temp = new self($elements, $use_model_name_prefix);
    }

    $this->old_select = self::merge_and_keep_keys_if_string($this->old_select, $temp->old_select);

    $this->select = self::clean_elements($this->old_select);
    return $this;
  }

  /**
   * Adds the ALIASES for the elements who have
   * @param array<int|string, string|array<int, string>> $elements
   */
  public static function clean_elements($elements)
  {
    $to_unset = [];
    foreach ($elements as $k => $v) {
      if (!is_string($k)) continue;

      if (is_array($v)) {
        foreach ($v as $v1) {
          $elements[] = "$k AS $v1";
        }

        $to_unset[] = $k;
        continue;
      }

      $elements[$k] = "$k AS $v";
    }

    foreach ($to_unset as $uns) unset($elements[$uns]);

    /**
     * @var string[] $elements
     */
    return array_values($elements);
  }

  /**
   * Prefix the result of the callable given
   * @param \Closure $callable Has two parameters, which is the $value and $key.
   * This callable returns a bool whether we should prefix or not
   * @param string $prefix
   * @param bool $replace_prefix
   */
  protected function prefix_condition($callable, $prefix = '', $replace_prefix = true)
  {
    $selects = $this->old_select;

    $k_s = [];
    foreach ($selects as $k => $v) {
      if (!is_array($v) && !$callable($v, $k)) continue;

      if (!is_string($k)) {
        $vall = $v;

        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];

        $selects[$v] = $prefix . $vall;
        $k_s[] = $k;
        continue;
      }

      if (!is_array($v)) {
        $vall = ($replace_prefix ? $k : $v);
        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];

        $selects[$k] = $prefix . $vall;
        continue;
      }

      foreach ($selects[$k] as $k1 => $v1) if ($callable($v1, $k)) {
        $vall = ($replace_prefix ? $k : $v1);
        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];
        $selects[$k][$k1] = $prefix . $vall;
      }
    }

    // Remove all the keys
    // Do it in another foreach to not get a concurrency exception
    foreach ($k_s as $key) unset($selects[$key]);

    $this->old_select = $selects;
    $this->select = self::clean_elements($selects);
    return $this;
  }

  /**
   * Prefix the result of the callable given
   * @param \Closure $callable Has two parameters, which is the $value and $key.
   * This callable returns a bool whether we should prefix or not
   * @param string $prefix
   * @param bool $replace_prefix
   */
  public function prefix_callable($callable, $prefix = '', $replace_prefix = true)
  {
    $selects = $callable($this->old_select);

    $k_s = [];
    foreach ($selects as $k => $v) {
      if (!is_string($k)) {
        $vall = $v;
        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];
        $selects[$v] = $prefix . $vall;
        $k_s[] = $k;
        continue;
      }

      if (!is_array($v)) {
        $vall = ($replace_prefix ? $k : $v);
        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];

        $selects[$k] = $prefix . $vall;
        continue;
      }

      foreach ($selects[$k] as $k1 => $v1) {
        $vall = ($replace_prefix ? $k : $v1);
        if (stripos($vall, '.') !== false) $vall = explode('.', $vall)[1];
        $selects[$k][$k1] = $prefix . $vall;
      }
    }

    // Remove all the keys
    // Do it in another foreach to not get a concurrency exception
    foreach ($k_s as $key) unset($selects[$key]);

    $this->old_select = $selects;
    $this->select = self::clean_elements($selects);
    return $this;
  }

  /**
   * Prefix everything inside the select element
   */
  public function prefix_everything($prefix = '', $replace_prefix = true)
  {
    return $this->prefix_callable(function ($e) {
      return $e;
    }, $prefix, $replace_prefix);
  }

  /**
   * Prefix only unprefixed element
   */
  public function prefix_new($prefix = '', $replace_prefix = true)
  {
    return $this->prefix_condition(function ($v, $k) {
      return is_int($k);
    }, $prefix, $replace_prefix);
  }

  public function decode(): string
  {
    return 'SELECT ' . implode(',', $this->select);
  }
}
