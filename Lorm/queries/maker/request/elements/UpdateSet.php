<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\request\interfaces\Element;
use Lorm\queries\maker\request\utils\Escaper;

class UpdateSet implements Element
{

  /**
   * Contains the col_name => value
   * @var array<string, mixed> $set_values
   */
  public $set_values = [];

  /**
   * Contains the table's name
   * @var string $table_name
   */
  public $table_name = "";

  /**
   * @param string $table_name
   * @param array<string, mixed> $set_values
   */
  public function __construct($table_name, $set_values)
  {
    $this->table_name = $table_name;
    $this->set_values = $set_values;
  }

  /**
   * 
   */
  public static function clean_values($array)
  {
    $new_arr = [];
    foreach ($array as $k => $v) {
      // Cleans the $v and add quotes if needed
      $v = Escaper::clean_and_add_quotes($v);

      $new_arr[] = "$k=$v";
    }
    return $new_arr;
  }

  public function decode(): string
  {
    return "UPDATE " . $this->table_name . " SET " . implode(", ", self::clean_values($this->set_values));
  }
}
