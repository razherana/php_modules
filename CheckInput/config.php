<?php

/**
 * Contains all default config for checkInput
 * You can modify this if you want changes in the default workings of the CheckInput
 * 
 * Use user_config for user configs
 */

use Lorm\queries\maker\queries\SortedQueryMaker;
use Lorm\queries\QueryExecutor;

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
    "optional" => fn($e) => !empty($e),
    "required" => fn($e) => !empty($e),
    "number" => fn($e) => is_numeric($e),
    "in" => [function ($e, $k, $all, $vars) {
      $array = explode(",", $vars[1]);

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
   * The separator used to separate the stringRule
   */
  "separator" => ";"
];
