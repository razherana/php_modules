<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlEndFor extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    $vars = $vars[0] ?? false;

    if (!in_array($vars, ['foreach', 'for'])) {
      throw new CompilerException("This is not a valable endfor component : $vars");
    }

    return "<?php end$vars; ?>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<\s*\/\s*(foreach|for)\s*\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "</foreach|for>";
  }
}
