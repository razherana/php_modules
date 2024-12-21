<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlEndBlock extends Component
{
  protected function get_uncompiled_syntax(): string
  {
    return "</block>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return "\\<\\/block\\>";
  }

  protected function get_compiled_syntax($vars): string
  {
    return "<?php \$___vars___->end_block(); ?>";
  }
}
