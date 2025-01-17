<?php

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

$array = ["a", "C", "t", "herana"];
$arrList = toArrList($array);

$arrList->removeAll(toArrList(["a", "t", "herana"]));

$arrList->forEach(function ($element) {
  echo $element . PHP_EOL;
});
