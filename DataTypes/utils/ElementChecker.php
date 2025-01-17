<?php

namespace DataTypes\utils;

/**
 * Use this trait for checking the type of elements
 * You can also use this trait for TemplateClass <T>
 */
trait ElementChecker
{

  /**
   * @var Types[]
   */
  private $types = [];

  /**
   * @return Types[]
   */
  public function getTypes(): array
  {
    return $this->types;
  }

  /**
   * Set this one time and use the same types for all elements
   * @param Types[] $types
   */
  protected function setTypes($types)
  {
    $this->types = $types;
  }

  /**
   * Verify the type of elements and return the elements
   * @param mixed ...$elements
   * @return array
   */
  protected function verifyThrow(...$elements): array
  {
    foreach ($elements as $index => $element)
      if (!isset($this->types[$index]) || !$this->types[$index]->compare($element))
        throw new \UnexpectedValueException("Element type is not valid, expecting : {$this->types[$index]} but found : " . Types::getTypeName($element));

    return $elements;
  }
}
