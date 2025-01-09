<?php

// Loads all config files into one array

$default = require __DIR__ . DIRECTORY_SEPARATOR . "config.php";

if (file_exists($fname = __DIR__ . DIRECTORY_SEPARATOR . "user_config.php"))
  $default = array_merge_recursive($default, include($fname));

return $default;