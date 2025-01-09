<?php

namespace ConfigReader;

use ConfigReader\exceptions\UnknownConfigException;

/**
 * This interface signals that the Component has a config file
 */
abstract class ConfigurableElement
{
  /** 
   * Contains the cached config
   * @var array<string, array<string, mixed>> $cached_config
   */
  protected static $cached_config = [];

  /**
   * Returns the config file
   */
  abstract public function config_file(): string;

  /**
   * Reads config
   * @param string $config_name
   */
  public function read_config($config_name)
  {
    return ConfigReader::get($this->config_file(), $config_name);
  }

  /**
   * Reads config from cache
   * @param string $config_name
   */
  public function read_cached_config($config_name)
  {
    if (!isset(static::$cached_config[static::class])) {
      static::$cached_config[static::class] = ConfigReader::get_all($this->config_file());
    }

    $content = static::$cached_config[static::class];

    if (!isset($content[$config_name])) {
      throw new UnknownConfigException($config_name, $this->config_file(), $this->config_file() . ".php");
    }

    return $content[$config_name];
  }

  /**
   * Get all of the configs and cache if possible
   */
  public function read_all_cache_config()
  {
    if (!isset(static::$cached_config[static::class])) {
      static::$cached_config[static::class] = ConfigReader::get_all($this->config_file());
    }

    return static::$cached_config[static::class];
  }

  /**
   * Get all of the configs and cache if possible
   */
  public function read_all_config()
  {
    return ConfigReader::get_all($this->config_file());
  }
}
