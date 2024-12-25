<?php

namespace Piewpiew\view\compiler\ast;

class TextLexiq
{
  public function __construct($content, $position)
  {
    $this->content = $content;
    $this->position = $position;
  }

  /**
   * The text content
   * @var string $content
   */
  public $content;

  /**
   * The position of the lexiq
   * @var int $position
   */
  public $position;

  public function replace($new_content) {
    $this->content = $new_content;
  }
}
