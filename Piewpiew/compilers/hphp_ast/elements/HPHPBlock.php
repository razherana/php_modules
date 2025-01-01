<?php

namespace Piewpiew\compilers\hphp_ast\elements;

class HPHPBlock
{
  public $name = "";
  public $content = "";
  
  public function __construct($name, $content)
  {
    $this->name = $name;
    $this->content = $content;
  }
}
