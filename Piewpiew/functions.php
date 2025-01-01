<?php

use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\compilers\php\PhpCompiler;
use Piewpiew\view\comm\ViewElement;
use Piewpiew\view\comm\ViewVars;
use Piewpiew\view\compiler\AbstractASTCompiler;
use Piewpiew\view\compiler\AbstractCompiler;
use Piewpiew\view\compiler\Compilation;
use Piewpiew\view\compiler\exceptions\CompilerException;

/**
 * This function is a fast helper to get a view element with it's content
 * @param string $view_name The view's name
 * @param string $compiler The compiler to use
 * @param array $elements Elements to use (block, templates, ...)
 * @return ViewElement Returns the compiled ViewElement
 */
function piewsub_view($view_name, $data = [], $compiler = PhpCompiler::class, $elements = [])
{
  // Creates the view element
  $view_el = new ViewElement($view_name);
  $view_el->vars = new ViewVars($data, $elements);

  // Creates the compiler
  $compiler = new $compiler($view_el);

  if(!is_a($compiler, AbstractCompiler::class))
    throw new CompilerException("The compiler specified isn't an AbstractCompiler but a " . $compiler::class);

  // Do compilation things
  new Compilation($compiler);

  // Returns the compiled view_element
  return $view_el;
}

/**
 * This function is a fast helper to directly print the view's content
 * @param string $view_name The view's name
 * @param string $compiler The compiler to use
 * @param array $elements Elements to use (block, templates, ...)
 * @return void Directly print the compiled ViewElement
 */
function piewsub($view_name, $data = [], $compiler = PhpCompiler::class, $elements = [])
{
  // Get the view
  $view = piewsub_view($view_name, $data, $compiler, $elements);

  // Echo the content
  echo $view->content;
}

/**
 * This function is a fast helper to get a view element with it's content.
 * Uses ast instead of substitution
 * @param string $view_name The view's name
 * @param string $compiler The compiler to use
 * @param array $elements Elements to use (block, templates, ...)
 * @return ViewElement Returns the compiled ViewElement
 */
function piewpiew_view($view_name, $data = [], $compiler = HPHPAstCompiler::class, $elements = [])
{
  // Creates the view element
  $view_el = new ViewElement($view_name);
  $view_el->vars = new ViewVars($data, $elements);

  // Creates the compiler
  $compiler = new $compiler($view_el);

  if(!is_a($compiler, AbstractASTCompiler::class))
    throw new CompilerException("The compiler specified isn't an AbstractASTCompiler but a " . $compiler::class);

  // Do compilation things
  new Compilation($compiler);

  // Returns the compiled view_element
  return $view_el;
}
/**
 * This function is a fast helper to directly print the view's content
 * @param string $view_name The view's name
 * @param string $compiler The compiler to use
 * @param array $elements Elements to use (block, templates, ...)
 * @return void Directly print the compiled ViewElement
 */
function piewpiew($view_name, $data = [], $compiler = HPHPAstCompiler::class, $elements = [])
{
  // Get the view
  $view = piewpiew_view($view_name, $data, $compiler, $elements);

  // Echo the content
  echo $view->content;
}
