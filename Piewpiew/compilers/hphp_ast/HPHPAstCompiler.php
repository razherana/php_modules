<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\html_php\HtmlViewVars;
use Piewpiew\view\compiler\AbstractASTCompiler;

class HPHPAstCompiler extends AbstractASTCompiler
{
  protected $dictionary;

  // Compiling only

  /**
   * Tells if a block is started.
   * @var bool
   */
  public $block = false;

  /**
   * Last index of open loop
   * @var array<string, int>
   */
  public $loop_index = [];

  /**
   * Nest index of loop
   * @var array<string, int>
   */
  public $loop_nest = [];

  public function __construct($view_element)
  {
    parent::__construct($view_element);
    $this->dictionary = new HPHPDictionary;
  }

  public function get_compiler_name(): string
  {
    return "hphp_ast";
  }

  public function get_extensions(): array
  {
    return ["hphp"];
  }

  public function get_view_var_class(): string
  {
    return HtmlViewVars::class;
  }
}
