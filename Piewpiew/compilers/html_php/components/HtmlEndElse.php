<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlEndElse extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    return "<?php endif; ?>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<\/\s*else\s*\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "</foreach|for>";
  }
}
