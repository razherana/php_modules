<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlElseIf extends Component
{
  protected function get_compiled_syntax($vars): string
  {
    if (empty($vars))
      throw new CompilerException("This shouldn't happen");

    return '<?php $___vars___->finish_wait("elseif"); elseif(' . $vars[0] . ') : ?>';
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<elseif\s+content\s*\=\s*"(.*?)"\s*\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<elseif content=\"@\">";
  }
}
