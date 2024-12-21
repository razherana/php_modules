<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlTemplate extends Component
{
  public function __construct(
    public $name = "",
    public $content = "",
    public $uses = []
  ) {}

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    $mode = "s";
    return "<template (\w+)(?:\s+use=\"(.*?)\"\s*)?>(.*?)<\/template>";
  }

  protected function get_uncompiled_syntax(): string
  {
    return "";
  }

  protected function get_compiled_syntax($vars): string
  {
    $name = trim(trim($vars[0], " "), "\"'");
    if (isset($vars[2])) {
      $content = addcslashes($vars[2], "'\\") ?? "";
      $uses = $vars[1];
    } else {
      $uses = "[]";
      $content = addcslashes($vars[1], "'\\") ?? "";
    }
    return "<?php \$___vars___->add_template('$name', '$content', $uses); ?>";
  }
}
