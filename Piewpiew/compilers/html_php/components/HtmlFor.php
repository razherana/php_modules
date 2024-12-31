<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlFor extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    if (empty($vars))
      throw new CompilerException("This shouldn't happen");

    $type = $vars[0];
    $content = $vars[1];

    return "<?php $type($content) : ?>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<(foreach|for)\s+loop\s*\=\s*"(.*?)"\s*>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<foreach|for loop=\"@\">";
  }
}
