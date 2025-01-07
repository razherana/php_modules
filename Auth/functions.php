<?php

use Auth\Auth;

function auth($name_of_auth = 0)
{
  return Auth::from_config($name_of_auth);
}
