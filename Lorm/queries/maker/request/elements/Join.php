<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\exceptions\JoinException;
use Lorm\queries\maker\request\Queryable;

class Join extends From
{
  private const TYPES = ['', 'LEFT', 'RIGHT', 'INNER'];

  public const NONE = 0, LEFT = 1, RIGHT = 2, INNER = 3;

  /**
   * Contains the Join type
   * @var int $join_type
   */
  private $join_type = self::NONE;

  /**
   * @param string|Queryable $table_or_subquery
   * @param ?string $as
   */
  public function __construct($table_or_subquery, $as = null, $join_type = self::NONE)
  {
    parent::__construct($table_or_subquery, $as);
    $this->join_type = $join_type;

    if (!in_array($join_type, array_keys(self::TYPES)))
      throw new JoinException("The JOIN type : '$join_type' doesn't exist", $this);
  }

  public function decode(): string
  {
    $type = self::TYPES[$this->join_type];

    // If $type is blank use blank. Else use the $type and add space and add JOIN
    $start = ($type == '' ? '' : ($type . ' ')) . "JOIN ";

    $as = "";
    if (!is_null($this->as)) $as = ' AS ' . $this->as;

    $from = $this->from;
    if ($this->is_query) $from = "($from)";

    return $start . $from . $as;
  }
}
