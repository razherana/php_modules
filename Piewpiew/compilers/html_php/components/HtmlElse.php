<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlElse extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    return '<?php else : ?>';
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<else\s*\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<else>";
  }
}
