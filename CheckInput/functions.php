<?php

use CheckInput\CheckInput;

/**
 * Check the $inputs given and tries every $rules until an error or something...
 * 
 * @param mixed $inputs An array, an object, ...
 * @param array<string,string> $rules The rules
 * @param string $access_method The method to access data from $inputs, 
 * you may wanna add an access method in user_config.php 
 * @return true|string True if all gone well, string if error is found, and the string is the error
 */
function check_input($inputs, $rules = [], $messages = [], $access_method = CheckInput::ARRAY_ASSOC)
{
  $a = new CheckInput($inputs, $access_method);
  foreach ($rules as $k => $v)
    $a->set($k, $v);
  return $a->check($messages);
}
