<?php

namespace Sezzion;

/**
 * This class contains helpers for session handling, like tempsend, tempget, etc.
 * It is a wrapper around the $_SESSION superglobal.
 */
class Sezzion
{

  private static $tempCacheContainer = [];

  public function __construct()
  {
    if (session_status() == PHP_SESSION_NONE) {
      if (headers_sent())
        throw new \Exception("Cannot start session, headers already sent.");
      session_start();
    }
  }

  /**
   * Set a session variable
   * @param string $key
   * @param mixed $value
   */
  public function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  /**
   * Get a session variable
   * @param string $key
   * @return mixed
   */
  public function get($key)
  {
    return @$_SESSION[$key];
  }

  /**
   * Set a session variable and delete it after the next request
   * @param string $key
   * @param mixed $value
   */
  public function tempset($key, $value)
  {
    $this->set($key, $value);
    $this->set($key . "_temp", true);
  }

  /**
   * Get a session variable and delete it
   * @param string $key
   * @return mixed
   */
  public function tempget($key)
  {
    if ($this->get($key . "_temp")) {
      $value = @$this->get($key);
      $this->delete($key);
      $this->delete($key . "_temp");
    } elseif (isset(self::$tempCacheContainer[$key])) {
      $value = self::$tempCacheContainer[$key];
      unset(self::$tempCacheContainer[$key]);
    } else {
      $value = null;
    }

    return $value;
  }

  /**
   * Delete a session variable
   * @param string $key
   */
  public function delete($key)
  {
    unset($_SESSION[$key]);
  }
}
