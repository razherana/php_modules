<?php

namespace Lorm\queries\maker\request\interfaces;

interface Element
{
  /**
   * Decodes the current Element into a string
   * understandable by sql
   * @return string
   */
  public function decode(): string;
}
