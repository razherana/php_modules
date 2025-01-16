<?php

/**
 * Contains all user config for checkInput,
 * You can add some configs you want to add. 
 * Maybe adding more access_methods?
 * Maybe adding more conditions to match your needs?
 */

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
  ],

  /**
   * Functions
   * They can be called inside a rule, 
   * that's why they have the $e and $k from checking.
   * 
   * @param $e The value of the element
   * @param $k The name of the element
   * @param $all All of the elements in the container
   * @param $vars All of the vars from the regex
   * @return mixed
   */
  "functions" => [
  ],

  /**
   * Access method
   */
  "access" => [
  ],
];
