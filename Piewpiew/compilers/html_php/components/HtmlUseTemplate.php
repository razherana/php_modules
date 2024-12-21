<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlUseTemplate extends Component
{
  protected function get_uncompiled_syntax(): string
  {
    return "<use-template @/>";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return "<use-template\s+(\w+)(?:\s+vars=\"(.*?)\"\s*)?\/\>";
  }

  protected function get_compiled_syntax($vars): string
  {
    $name = trim(trim($vars[0], " "), "\"'");
    $var = trim($vars[1] ?? '[]', " ");
    return "<?php \$___vars___->use_template('$name', $var); ?>";
  }
}
