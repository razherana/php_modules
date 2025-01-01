<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\hphp_ast\elements\HPHPBlock;
use Piewpiew\compilers\hphp_ast\elements\HPHPTemplate;
use Piewpiew\view\comm\ViewVars;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HPHPViewVars extends ViewVars
{
  public $block_started = false;

  public $template_started = false;

  public $join_content = false;

  public function start_block($block_name)
  {
    if ($this->block_started) {
      throw new CompilerException("A block is already started, cannot start another one");
    }
    $this->block_started = true;

    if (!isset($this->elements[HPHPBlock::class])) {
      $this->elements[HPHPBlock::class] = [];
    }
    $this->elements[HPHPBlock::class][$block_name] = new HPHPBlock($block_name, "");
    ob_start();
  }

  public function end_block()
  {
    $exc = new CompilerException("The block is not started yet, cannot end this block");

    if (!$this->block_started || empty($this->elements[HPHPBlock::class]))
      throw $exc;

    $last = array_key_last($this->elements[HPHPBlock::class]);

    if (!empty($this->elements[HPHPBlock::class][$last]->content)) {
      throw $exc;
    }

    $block_content = ob_get_clean();

    $this->elements[HPHPBlock::class][$last]->content = $block_content;
    $this->block_started = false;
  }

  public function include_block($view_name, $variables = [])
  {
    $view_element = piewpiew_view($view_name, $variables, HPHPAstCompiler::class);
    echo $view_element->content;
  }

  public function use($block_name)
  {
    if (empty($this->elements[HPHPBlock::class]) || !isset($this->elements[HPHPBlock::class][$block_name])) {
      return;
    }
    echo $this->elements[HPHPBlock::class][$block_name]->content;
  }

  public function add_template($template_name, $content, $uses = [])
  {
    if (!isset($this->elements[HPHPTemplate::class])) {
      $this->elements[HPHPTemplate::class] = [];
    }
    $this->elements[HPHPTemplate::class][$template_name] = new HPHPTemplate($template_name, $content, $uses);
  }

  public function use_template($template_name, $vars = [])
  {
    if (empty($this->elements[HPHPTemplate::class]) || !isset($this->elements[HPHPTemplate::class][$template_name])) {
      throw new CompilerException("This template doesn't exist : '$template_name'");
    }

    // We make this a random long name so it has low probability it collides with the var_name there
    $__content__content__content__content__content__content__ = $this->elements[HPHPTemplate::class][$template_name]->content;

    // We extract the vars
    extract($this->elements[HPHPTemplate::class][$template_name]->uses);
    extract($vars);

    // We eval the code
    eval("?>$__content__content__content__content__content__content__");
  }

  public function join($view_name, $vars = [])
  {
    $this->join_content = [
      $view_name,
      $vars
    ];
  }

  public function use_join()
  {
    if ($this->join_content === false) {
      throw new CompilerException("Cannot use_join because there is no join to use");
    }

    piewpiew(
      $this->join_content[0],
      $this->join_content[1],
      HPHPAstCompiler::class,
      // We add only the templates and blocks to the var
      array_filter($this->elements, function ($k) {
        return in_array($k, [HPHPTemplate::class, HPHPBlock::class]);
      }, ARRAY_FILTER_USE_KEY)
    );
  }
}
