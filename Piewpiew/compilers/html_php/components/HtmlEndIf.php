<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;
use Piewpiew\view\compiler\exceptions\CompilerException;

class HtmlEndIf extends Component
{
  /**
   * Translate the $content to be usable in compiled_syntax
   * @param string[] $content
   */
  protected function translate($content)
  {
    switch ($content[0] ?? false) {
      case "elseif":
        return '// elseif block needs to follow';
      case "else":
        return '// else block needs to follow';
      case false:
        return "endif;";
      default:
        throw new CompilerException("This endif component doesn't exist");
    }
  }

  protected function get_compiled_syntax($vars): string
  {
    return '<?php ' . $this->translate($vars) . ' ?>';
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return '\<\s*\/\s*(?:if|elseif|auth|guest)(?:\s+(.*?)\s*)?\>';
  }

  protected function get_uncompiled_syntax(): string
  {
    return "";
  }
}
