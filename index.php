<?php

use Lorm\BaseModel;
use Lorm\Lorm;
use Lorm\queries\maker\queries\SortedQueryMaker;
use Lorm\queries\QueryExecutor;

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

$pdo = new PDO("mysql:host=localhost;dbname=restaurant", 'razherana', '');
Lorm::set_pdo($pdo);

class Serveur extends BaseModel
{
  /**
   * The primary key column
   * @var string
   */
  public $primary_key = "idServeur";

  /**
   * The model's table name
   * @var string
   */
  public $table = "Serveur";

  /**
   * The model's column
   * @var string[]
   */
  public $columns = ["idServeur", "nomServeur", "dateNaissance"];

  /**
   * All of the relations to pre-load
   * @var string[]
   */
  public $eager_load = [];

  protected function get_cast(): array
  {
    return [
      "dateNaissance" => fn($e) => new DateTime($e),
    ];
  }

  protected function set_cast(): array
  {
    return [
      "dateNaissance" => fn($e) => $e->format("Y-m-d H:i:s"),
    ];
  }
}
