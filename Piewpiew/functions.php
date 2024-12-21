<?php

use Piewpiew\compilers\php\PhpCompiler;
use Piewpiew\view\comm\ViewElement;
use Piewpiew\view\comm\ViewVars;
use Piewpiew\view\compiler\Compilation;

/**
 * This function is a fast helper to get a view element with it's content
 * @param string $view_name The view's name
 * @param string $compiler The compiler to use
 * @param array $elements Elements to use (block, templates, ...)
 * @return ViewElement Returns the compiled ViewElement
 */
function piewpiew_view($view_name, $data = [], $compiler = PhpCompiler::class, $elements = [])
{
  // Creates the view element
  $view_el = new ViewElement($view_name);
  $view_el->vars = new ViewVars($data, $elements);

  // Creates the compiler
  $compiler = new $compiler($view_el);

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
function piewpiew($view_name, $data = [], $compiler = PhpCompiler::class, $elements = [])
{
  // Get the view
  $view = piewpiew_view($view_name, $data, $compiler, $elements);

  // Echo the content
  echo $view->content;
}
