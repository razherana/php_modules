<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\hphp_ast\HPHPViewVars;
use Piewpiew\view\compiler\AbstractASTCompiler;

class HPHPAstCompiler extends AbstractASTCompiler
{
  protected $dictionary;

  // Compiling only

  /**
   * Tells if a template has been used
   * @var bool
   */
  public $template = 0;

  /**
   * Tells if a join has been used
   * @var bool
   */
  public $join = false;

  /**
   * Tells if a block is started.
   * @var bool
   */
  public $block = 0;

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
    return HPHPViewVars::class;
  }
}
