<?php

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

$array = ["a", "C", "t", "herana"];
$arrList = toArrList($array);

$arrList->map(function ($element) {
  return strtoupper($element);
})->forEach(function ($element) {
  echo $element . PHP_EOL;
});
