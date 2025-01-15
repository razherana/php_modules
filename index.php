<?php

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

var_dump(check_input(['password' => 'herana', 'confirm' => 'herana', "test" => 10], [
  "password" => "required;",
  "confirm" => "required;eq:from(password);",
  "test" => "required;sup:10"
], [
  "confirm:eq" => "Password isn't equal from Confirm password"
])[0] ?? true);
