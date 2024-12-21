<?php

namespace Piewpiew\compilers\html_php\components;

use Piewpiew\view\compiler\components\Component;

class HtmlJoin extends Component
{
  public function compile_all($uncompiled_content): string
  {
    // Checks if this view has a htmljoin component
    $compiled = parent::compile_all($uncompiled_content);

    // If the compiled != uncompiled, then a join has been found
    // We check the strlen first to minimize calculations
    if (strlen($compiled) == strlen($uncompiled_content) && $compiled == $uncompiled_content) {
      return $compiled;
    }

    // The value to insert
    $join = '$___vars___->use_join(); ?>';

    /* number of <?php or < ? or < ?= tags */
    $open = preg_match_all("/(\<\?php|\<\?|\<\?\=)/", $compiled);
    /* number of ?> tags */
    $closed = preg_match_all("/(\?\>)/", $compiled);

    // Append the value
    $compiled .=
      // If open > closed, there is an open php tag without a closing one so we add a closing tag
      ($open > $closed ? " ?>" : "")
      . "<?php $join";

    return $compiled;
  }

  protected function get_uncompiled_syntax(): string
  {
    return "<join @ vars=\"@\" />";
  }

  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    return ("\<join\s+(\S+)(?:\s+vars=\"(.*?)\"\s*)?\/\>");
  }

  protected function get_compiled_syntax($vars): string
  {
    $name = trim($vars[0], "\"'");
    $name = str_replace(".", "/", $name);
    $variables = trim($vars[1] ?? "[]", " ");
    return "<?php \$___vars___->join('$name', $variables); ?>";
  }
}
