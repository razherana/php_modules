<?php

namespace CheckInput\exceptions;

use Exception;

class ConfigNotFoundException extends Exception
{
  public function __construct($message = '')
  {
    parent::__construct($message, 1);
  }
}
