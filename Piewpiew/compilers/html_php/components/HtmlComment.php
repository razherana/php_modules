<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlComment extends Component
{
  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    $mode = "s";
    return "\<comment\s*\/?\>(.*?)<\/\s*comment\s*\>";
  }

  protected function get_uncompiled_syntax(): string
  {
    return "";
  }

  protected function get_compiled_syntax($vars): string
  {
    $comments = $vars[0];
    return "<?php /*
      $comments
    */?>";
  }
}
