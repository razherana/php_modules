<?php

namespace Lorm\queries;

use Lorm\Lorm;
use PDO;

class QueryExecutor
{
  /**
   * @param PDO $pdo
   */
  private $pdo;

  private $terms = [
    "insert into",
    "update",
    "delete",
    "truncate"
  ];

  private function __construct() {
    $this->pdo = Lorm::get_pdo();
  }

  /**
   * Execute a query
   * @param string $q
   * @param array<array<int, mixed>> $params 
   * @return bool|array<array<string, mixed>>
   */
  public static function execute($q, $params = [])
  {
    /**
     * @var PDO $pdo
     */
    $example = new static();
    $pdo = $example->pdo;

    $prepared = $pdo->prepare($q);
    $res = $prepared->execute($params);

    $execute_only = false;

    foreach ((new static())->terms as $term)
      if (stripos($q, $term) !== false) {
        $execute_only = true;
        break;
      }

    if ($execute_only) return $res;

    return $prepared->fetchAll(PDO::FETCH_ASSOC);
  }
}