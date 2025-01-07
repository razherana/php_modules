<?php

namespace ConfigReader;

use ConfigReader\exceptions\ConfigReadingException;
use ConfigReader\exceptions\UnknownConfigException;

class ConfigReader
{
  /**
   * @param string $file_name
   * @param string $config_name
   */
  public static function get($file_name, $config_name): mixed
  {
    $full_path = $file_name . '.php';

    if (!file_exists($full_path)) {
      throw new ConfigReadingException("The file '$file_name' with a full path of '$full_path' doesn't exist");
    }

    /** @var array<string, mixed> $content */
    $content = (include($full_path));

    if (!isset($content[$config_name])) {
      throw new UnknownConfigException($config_name, $file_name, $full_path);
    }

    return $content[$config_name];
  }

  /**
   * @param string $file_name
   */
  public static function get_all($file_name): array
  {
    $full_path = $file_name . '.php';

    if (!file_exists($full_path)) {
      throw new ConfigReadingException("The file '$file_name' with a full path of '$full_path' doesn't exist");
    }

    return (include($full_path));
  }
}
