<?php

namespace ConfigReader\exceptions;

class UnknownConfigException extends ConfigException
{
  private $config_name, $config_file, $config_full_path;

  public function __construct($config_name, $config_file, $config_full_path)
  {
    $this->config_name = $config_name;
    $this->config_file = $config_file;
    $this->config_full_path = $config_full_path;

    parent::__construct("The config '$config_name' in '$config_file' with a full path : '$config_full_path' doesn't exist");
  }
}
