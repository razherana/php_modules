<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlBlock extends Component
{
  public function __construct(
    public $name = "",
    public $content = ""
  ) {}

  protected function get_compiled_syntax($vars): string
  {
    if (empty($vars))
      throw new CompilerException("This block doesn't have a name");

    $vars = $vars[array_key_first($vars)];

    // We first trim whitespace then after we trim the "" or '' to not remove the spaces inside the quotes
    return '<?php $___vars___->start_block("' . trim(trim($vars, " "), "\"'") . '"); ?>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<block @>";
  }
}
