<?php

/**
 * This is a file that contains your modules
 * It autoloads automatically every modules so don't worry
 */

$modules = [
  // If the module_folder == module_namespace
  // "module_folder" 

  // If the module_folder != module_namespace
  // "module_namespace" => "module_folder"

  "ConfigReader",
  "CheckInput",
  "Piewpiew",
  "Lorm",
  "Auth",
  "DataTypes",
  "Sezzion"
];

foreach ($modules as $k => $v) {
  $NAMESPACE = is_numeric($k) ? $v : $k;
  $FOLDER = $v;

  $datas = [];

  if (file_exists($initFile = __DIR__ . DIRECTORY_SEPARATOR . $FOLDER . DIRECTORY_SEPARATOR . "init.php"))
    $datas = require $initFile;

  if (isset($datas['requires']))
    foreach ($datas['requires'] as $requirement)
      if (!in_array($requirement, array_keys($modules)) && !in_array($requirement, array_values($modules)))
        throw new Exception("$NAMESPACE requires $requirement but is missing");

  spl_autoload_register(function ($className) use ($NAMESPACE, $FOLDER) {
    $pos = 0;
    if (!(str_starts_with($className, $NAMESPACE) && ($pos = strpos($className, $NAMESPACE)) !== false))
      return;

    $fileName = substr($className, $pos + strlen($NAMESPACE) + 1);
    $fileName = $FOLDER . DIRECTORY_SEPARATOR . trim(str_replace("\\", DIRECTORY_SEPARATOR, $fileName), "\\/") . ".php";

    if (file_exists($fileName = __DIR__ . DIRECTORY_SEPARATOR . trim($fileName, DIRECTORY_SEPARATOR)))
      include $fileName;
    else
      throw new Exception("Cannot find the class $className in $fileName");
  });
}
