<?php

use Auth\Auth;
use Lorm\BaseModel;
use Lorm\Lorm;

$ds = DIRECTORY_SEPARATOR;
require_once __DIR__ . $ds . "modules.php";

class User extends BaseModel
{
  /**
   * The primary key column
   * @var string
   */
  public $primary_key = "id";

  /**
   * The model's table name
   * @var string
   */
  public $table = "users";

  /**
   * The model's column
   * @var string[]
   */
  public $columns = [
    "id",
    "name",
    "email",
    "password"
  ];

  /**
   * All of the relations to pre-load
   * @var string[]
   */
  public $eager_load = [];

}
