<?php

namespace Piewpiew\view\compiler;

use Piewpiew\view\View;

class Compilation
{
  /**
   * Contains the compiler for the view
   * @var (AbstractCompiler|AbstractASTCompiler) $compiler
   */
  protected $compiler;

  /**
   * @param (AbstractCompiler|AbstractASTCompiler) $compiler
   */
  public function __construct($compiler, $load_view = true)
  {
    $this->compiler = $compiler;

    if ($load_view)
      $this->load_view();
  }

  protected function load_view()
  {
    $view_var_class = ($this->compiler->get_view_var_class());

    // Sets the view var
    $view_var = $this->compiler->view_element->vars = new $view_var_class($this->compiler->view_element->vars);

    // Saves the view var to the View
    View::$view_vars[$this->compiler->view_element->view_name] = $view_var;

    $this->compiler->compile_and_save_content();
  }
}
