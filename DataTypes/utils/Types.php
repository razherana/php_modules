<?php

namespace DataTypes\utils;

class Types
{
  public const STRING = "string";
  public const INT = "int";
  public const FLOAT = "float";
  public const BOOL = "bool";
  public const ARRAY = "array";
  public const RESOURCE = "resource";
  public const NULL = "NULL";
  public const UNKNOWN = "unknown";

  private $currentType;

  public function __construct($name)
  {
    $this->currentType = $name;
  }

  public function getName(): string
  {
    return $this->currentType;
  }

  public function equals($type): bool
  {
    return $this->currentType === $type->getName();
  }

  public static function getTypeName($value): string
  {
    if (is_string($value))
      return self::STRING;
    if (is_int($value))
      return self::INT;
    if (is_float($value))
      return self::FLOAT;
    if (is_bool($value))
      return self::BOOL;
    if (is_array($value))
      return self::ARRAY;
    if (is_object($value))
      return $value::class;
    if (is_resource($value))
      return self::RESOURCE;
    if (is_null($value))
      return self::NULL;

    return self::UNKNOWN;
  }

  public static function getType($value): Types
  {
    return new Types(self::getTypeName($value));
  }

  public function compare($value): bool
  {
    return self::getTypeName($value) === $this->currentType;
  }

  public function __toString(): string
  {
    return $this->currentType;
  }

  public static function compareTypes($value1, $value2): bool
  {
    return self::getType($value1)->compare($value2);
  }
}
