<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\request\interfaces\Element;
use Lorm\queries\maker\request\utils\Escaper;

class InsertInto implements Element
{
  private $datas = [], $table_name = "", $columns = [];

  public function decode(): string
  {
    if (count($this->columns) <= 0)
      return 'INSERT INTO ' . $this->table_name . ' VALUES (' . implode(', ', $this->datas) . ')';

    return 'INSERT INTO ' . $this->table_name . ' (' . implode(', ', $this->columns) . ') VALUES (' . implode(', ', $this->datas) . ')';
  }

  /**
   * @param string $table_name
   * @param string[] $values
   */
  public function __construct($table_name, $values)
  {
    $this->table_name = $table_name;
    $this->datas = $values;
    $this->decode_datas();
  }

  private function decode_datas()
  {
    $datas = $this->datas;
    $columns = [];

    foreach ($datas as $column => $value) {
      $datas[$column] = Escaper::clean_and_add_quotes($value);

      if (is_string($column)) {
        $columns[] = $column;
      }
    }

    $this->columns = $columns;
    $this->datas = array_values($datas);
  }
}
