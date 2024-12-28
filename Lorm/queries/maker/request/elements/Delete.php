<?php

namespace Lorm\queries\maker\request\elements;

use Lorm\queries\maker\request\interfaces\Element;

class Delete implements Element
{
  public function decode(): string
  {
    return 'DELETE';
  }
}
