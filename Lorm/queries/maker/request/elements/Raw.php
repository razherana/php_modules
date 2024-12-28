<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\request\interfaces\Element;

class Raw implements Element
{
  private $content = '';

  /**
   * @param string $content
   */
  public function __construct($content)
  {
    $this->content = $content;
  }

  public function decode(): string
  {
    return $this->content;
  }
}
