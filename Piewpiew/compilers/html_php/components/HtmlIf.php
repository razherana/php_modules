<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlIf extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    if (empty($vars))
      throw new CompilerException("This shouldn't happen");

    // We first trim whitespace then after we trim the "" or '' to not remove the spaces inside the quotes
    return '<?php if(' . $vars[0] . ') : ?>';
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<if\s+content\s*\=\s*"(.*?)"\s*\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<if content=\"@\">";
  }
}
