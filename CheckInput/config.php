<?php

/**
 * Contains all default config for checkInput
 * You can modify this if you want changes in the default workings of the CheckInput
 * 
 * Use user_config for user configs
 */

use Lorm\queries\maker\queries\SortedQueryMaker;
use Lorm\queries\QueryExecutor;

$anon_funs = [
  'transform_into_value' => function ($string, $e, $k) {
    $array = trim($string);
    $array = array_map(fn($el) => trim($el), explode(",", $array));
    foreach ($array as $key => $value) {
      if (preg_match('/@v/', $value) === 1) {
        $array[$key] = $e;
      } elseif (preg_match('/@k/', $value) === 1) {
        $array[$key] = $k;
      }
    }
    return $array;
  },
  'cast_value' => function ($array) {
    foreach ($array as $k => $value) {
      $value = trim($value);
      if (stripos("\"'`", $value[0]) !== false)
        $value = trim($value, "\"'`");
      elseif (is_numeric($value)) {
        if (ctype_digit(abs($value)))
          $value = intval($value);
        else
          $value = doubleval($value);
      }
      $array[$k] = $value;
    }
    return $array;
  }
];

return [
  /**
   * Contains checking functions
   * @param $e The value of the element
   * @param $k The name of the element
   * @param $all All of the elements in the container
   * @param $vars All of the vars from the TestInput instance
   * @param $testInput The TestInput instance itself
   * @var $this The $this var is binded to the TestInput
   * @return bool
   */
  "checking" => [
    "optional" => fn($e) => $e !== null,
    "required" => fn($e) => $e !== null,
    "number" => fn($e) => is_numeric($e),
    "in" => [function ($e, $k, $all, $vars) use ($anon_funs) {
      $array = $anon_funs["cast_value"](explode(",", $vars[1]));
      return in_array($e, $array, true);
    }, "/in\:(.*)/"],
    "email" => fn($e) => preg_match("/[a-zA-Z0-9._%+-]{3,}@[a-zA-Z0-9-]{3,}\.[a-zA-Z]{2,}/", $e) === 1,
    "unique" => [
      fn($e, $k, $all, $vars) => count(QueryExecutor::execute((new SortedQueryMaker)->select(["*"])->from($vars[2])->where($vars[1], '=', $e)->decode_query())) <= 0,
      "/unique\:(\w+),(\w+)/"
    ],
    "exists" => [
      fn($e, $k, $all, $vars) => count(QueryExecutor::execute((new SortedQueryMaker)->select(["*"])->from($vars[2])->where($vars[1], '=', $e)->decode_query())) >= 1,
      "/exists\:(\w+),(\w+)/"
    ],
    "sup" => [
      fn($e, $k, $all, $vars) => $e > $vars[1],
      "/sup:(.*)/"
    ],
    "eqsup" => [
      fn($e, $k, $all, $vars) => $e >= $vars[1],
      "/eqsup:(.*)/"
    ],
    "inf" => [
      fn($e, $k, $all, $vars) => $e < $vars[1],
      "/inf:(.*)/"
    ],
    "eqinf" => [
      fn($e, $k, $all, $vars) => $e <= $vars[1],
      "/eqinf:(.*)/"
    ],
    "eq" => [
      fn($e, $k, $all, $vars) => $e == $vars[1],
      "/eq:(.*)/"
    ]
  ],

  /**
   * Contains the message functions, 
   * it is binded to the TestInput object to access vars and names etc...
   * @param $e The value of the element
   * @param $k The name of the element
   * @param $all All of the elements in the container
   * @param $vars All of the vars from the TestInput instance
   * @param $testInput The TestInput instance itself
   * @var $this The $this var is binded to the TestInput
   * @return string|true
   */
  "messages" => [
    "required" => fn($e, $k, $all) => "$k is required",
    "number" => fn($e, $k, $all) => "$k needs to be a number",
    "in" => fn($e, $k, $all, $vars) => "$k isn't in {$vars[1]}",
    "email" => fn($e, $k) => "$k isn't an email",
    "unique" => fn($e, $k) => "$e from $k already exists",
    "exists" => fn($e, $k) => "$k doesn't exists",
    "sup" => fn($e, $k, $all, $vars) => "$k isn't superior to {$vars[1]}",
    "eqsup" => fn($e, $k, $all, $vars) => "$k isn't superior or equal to {$vars[1]}",
    "inf" => fn($e, $k, $all, $vars) => "$k isn't inferior to {$vars[1]}",
    "eqinf" => fn($e, $k, $all, $vars) => "$k isn't inferior or equal to {$vars[1]}",
    "eq" => fn($e, $k, $all, $vars) => "$k isn't equal to {$vars[1]}",
    /**
     * If empty but optional, then we just return true
     */
    "optional" => fn() => true
  ],

  /**
   * Access method
   */
  "access" => [
    "assoc" => fn($e, $all) => $all[$e],
    "arrow" => fn($e, $all) => $all->{$e},
  ],

  /**
   * Functions
   * They can be called inside a rule, 
   * that's why they have the $e and $k from checking
   * @param $e The value of the element
   * @param $k The name of the element
   * @param $all All of the elements in the container
   * @param $vars All of the vars from the regex
   * @return mixed
   */
  "functions" => [
    "use" => [fn($e, $k, $all, $vars) => $anon_funs['cast_value'](($anon_funs['transform_into_value']($vars[1], $e, $k)))[0], "/use\((.*)\)/"],
    "from" => [fn($e, $k, $all, $vars) => $all[$vars[1]], "/from\((.*)\)/"],
    "sum" => [function ($e, $k, $all, $vars) use ($anon_funs) {
      $array = $anon_funs['cast_value'](array_map(fn($ell) => trim($ell), explode(",", $vars[1])));
      return array_sum($array);
    }, "/sum\((.*)\)/"],
    "max" => [function ($e, $k, $all, $vars) use ($anon_funs) {
      $array = $anon_funs['cast_value'](array_map(fn($ell) => trim($ell), explode(",", $vars[1])));
      return max(...$array);
    }, "/max\((.*)\)/"],
  ],

  /**
   * The separator used to separate the stringRule
   */
  "separator" => ";"
];
