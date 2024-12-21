<?php

namespace Piewpiew\compilers\html_php;

use Piewpiew\compilers\html_php\components\HtmlBlock;
use Piewpiew\compilers\html_php\components\HtmlTemplate;
use Piewpiew\view\comm\ViewVars;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlViewVars extends ViewVars
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

    if (!isset($this->elements[HtmlBlock::class])) {
      $this->elements[HtmlBlock::class] = [];
    }
    $this->elements[HtmlBlock::class][$block_name] = new HtmlBlock($block_name, "");
    ob_start();
  }

  public function end_block()
  {
    $exc = new CompilerException("The block is not started yet, cannot end this block");

    if (!$this->block_started || empty($this->elements[HtmlBlock::class]))
      throw $exc;

    $last = array_key_last($this->elements[HtmlBlock::class]);

    if (!empty($this->elements[HtmlBlock::class][$last]->content)) {
      throw $exc;
    }

    $block_content = ob_get_clean();

    $this->elements[HtmlBlock::class][$last]->content = $block_content;
    $this->block_started = false;
  }

  public function include_block($view_name, $variables = [])
  {
    $view_element = piewpiew_view($view_name, $variables, HtmlCompiler::class);
    echo $view_element->content;
  }

  public function use($block_name)
  {
    if (empty($this->elements[HtmlBlock::class]) || !isset($this->elements[HtmlBlock::class][$block_name])) {
      return;
    }
    echo $this->elements[HtmlBlock::class][$block_name]->content;
  }

  public function add_template($template_name, $content, $uses = [])
  {
    if (!isset($this->elements[HtmlTemplate::class])) {
      $this->elements[HtmlTemplate::class] = [];
    }
    $this->elements[HtmlTemplate::class][$template_name] = new HtmlTemplate($template_name, $content, $uses);
  }

  public function use_template($template_name, $vars = [])
  {
    if (empty($this->elements[HtmlTemplate::class]) || !isset($this->elements[HtmlTemplate::class][$template_name])) {
      throw new CompilerException("This template doesn't exist : '$template_name'");
    }

    // We make this a random long name so it has low probability it collides with the var_name there
    $__content__content__content__ = $this->elements[HtmlTemplate::class][$template_name]->content;

    // We extract the vars
    extract($this->elements[HtmlTemplate::class][$template_name]->uses);
    extract($vars);

    // We eval the code
    eval("?>$__content__content__content__");
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
      HtmlCompiler::class,
      // We add only the templates and blocks to the var
      array_filter($this->elements, function ($k) {
        return in_array($k, [HtmlTemplate::class, HtmlBlock::class]);
      }, ARRAY_FILTER_USE_KEY)
    );
  }
}
