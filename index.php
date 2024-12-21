<?php

use Piewpiew\compilers\html_php\HtmlCompiler;

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

$datas = [
  [
    "id" => 1,
    "nom" => "razafindralambo",
    "prenom" => "herana",
  ],
  [
    "id" => 2,
    "nom" => "andriamarison",
    "prenom" => "fanilo hasina",
  ],
];

piewpiew("test", compact("datas"), HtmlCompiler::class);
