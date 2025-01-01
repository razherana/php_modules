<?php

namespace Piewpiew\compilers\hphp_ast\elements;

class HPHPTemplate
{
  public $name = "";
  public $content = "";
  public $uses = [];

  public function __construct($name, $content, $uses) {
    $this->name = $name;
    $this->content = $content;
    $this->uses = $uses;
  }
}
