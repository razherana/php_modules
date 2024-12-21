<?php

namespace Lorm;

use Exception;
use PDO;

class Lorm {
  /** @var PDO $pdo */
  private static $pdo = null; 

  /**
   * @param PDO $pdo
   */
  public static function set_pdo($pdo) {
    self::$pdo = $pdo;
  }

  /**
   * @param PDO $pdo
   */
  public static function get_pdo() {
    if(self::$pdo === null)
      throw new Exception("The Lorm pdo var is not initialized, use Lorm::set_pdo(\$pdo)", 1);
    return self::$pdo;
  }  
}