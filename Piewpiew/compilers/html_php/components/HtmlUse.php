<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlUse extends Component
{
  protected function get_uncompiled_syntax(): string
  {
    return "<use @/>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return "\<use\s+(\S+)\s+\/?\>";
  }

  protected function get_compiled_syntax($vars): string
  {
    $name = trim(trim($vars[0], " "), "\"'");
    return "<?php \$___vars___->use('$name'); ?>";
  }
}
