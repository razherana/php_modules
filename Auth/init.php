<?php

define("AUTH_DIR", __DIR__);

require __DIR__ . DIRECTORY_SEPARATOR . "functions.php";

return [
  "requires" => [
    "ConfigReader",
    "Lorm"
  ]
];