<?php

namespace Lorm\queries;

class QueryUtil
{
  public static function where($el1, $el2, $operator = "="): string
  {
    if ($el2 === null)
      $operator = "IS";
    return "$el1 $operator $el2";
  }

  public static function where_parameter($el1, $el2, $operator = "="): string
  {
    if ($el2 === null)
      $operator = "IS";
    return "$el1 $operator :$el1";
  }

  public static function where_parameter_all($list, $default_operator = "=", $separator = "AND"): string
  {
    $q = [];
    foreach ($list as $arr) {
      $op = $default_operator;
      if (isset($arr[2]))
        $op = $arr[2];
      $q[] = self::where_parameter($arr[0], $arr[1], $op);
    }
    return implode(" $separator ", $q);
  }

  public static function where_all($list, $default_operator = "=", $separator = "AND"): string
  {
    $q = [];
    foreach ($list as $arr) {
      $op = $default_operator;
      if (isset($arr[2]))
        $op = $arr[2];
      $q[] = self::where($arr[0], $arr[1], $op);
    }
    return implode(" $separator ", $q);
  }
}
