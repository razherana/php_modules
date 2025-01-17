<?php

use DataTypes\usable\ArrList;
use DataTypes\utils\Types;

/**
 * Convert an array to an ArrList.
 * Each element of the array must have the same type.
 * If the array is empty, the type is UNKNOWN.
 * @param array $elements
 * @return ArrList
 */
function toArrList($elements = []): ArrList
{
  $elements = array_values($elements);
  for ($i = 0; $i < count($elements) - 1; $i++)
    if (!Types::getType($elements[$i])->equals(Types::getType($elements[$i + 1])))
      throw new \InvalidArgumentException("Type mismatch in array : " . Types::getType($elements[$i]) . " !== " . Types::getType($elements[$i + 1]));

  $arrList = new ArrList(empty($elements) ? new Types(Types::UNKNOWN) : Types::getType($elements[0]));
  foreach ($elements as $element)
    $arrList->add($element);
  return $arrList;
}
