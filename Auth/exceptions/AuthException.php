<?php

namespace Auth\exceptions;

use Exception;

class AuthException extends Exception
{
  public $auth;

  public function __construct($message, $auth = null)
  {
    $this->auth = $auth;
    parent::__construct($message);
  }
}
