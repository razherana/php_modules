<?php

namespace Piewpiew\view\compiler\components;

/**
 * Represents a block of uncompiled code
 */
abstract class Component
{
  /**
   * Gets the uncompiled syntax
   * i.e: syntax @ other_syntax
   */
  abstract protected function get_uncompiled_syntax(): string;

  /**
   * Gets the compiled syntax with vars
   * i.e: php_syntax @ other_php_syntax
   * 
   * @param array $vars The variables to use in the compiled syntax 
   */
  abstract protected function get_compiled_syntax($vars): string;

  /**
   * Get the regex format of the uncompiled syntax
   * @return string The uncompiled syntax
   */
  protected function get_uncompiled_syntax_regex($uncompiled_syntax, &$mode): string
  {
    // Uses the preg_quote if not overrided
    // Replace $ with a regex expression -> (.*) 
    return str_replace('@', '(\S+)', preg_quote($uncompiled_syntax));
  }

  /**
   * By default this returns positions of comments
   * @param string $content The uncompiled content
   * @return int[]
   */
  protected function get_haltcompiled_positions($content): array
  {
    $positions = [];

    $regex = $this->get_uncompiled_syntax_regex($this->get_uncompiled_syntax(), $mode);

    // We get every component inside multi-line comments /* */
    $result = preg_match_all("/\/\*.*($regex).*\*\//s", $content, $matches, PREG_OFFSET_CAPTURE);

    if ($result > 0) foreach ($matches[1] as $match)
      $positions[] = $match[1];

    return $positions;
  }

  /**
   * @param string $uncompiled_content
   */
  public function compile_all($uncompiled_content): string
  {
    $uncompiled = $this->get_uncompiled_syntax();

    $mode = "";

    $uncompiled_regex = $this->get_uncompiled_syntax_regex($uncompiled, $mode);

    $unavailable_positions = $this->get_haltcompiled_positions($uncompiled_content);

    // Initialize all of the datas
    $offset = 0;
    $match = [];
    $result = preg_match("/$uncompiled_regex/" . $mode, $uncompiled_content, $match, PREG_OFFSET_CAPTURE, $offset);

    while ($result) {
      // Saves the start of the syntax
      $offset_start = $match[0][1];

      // Checks if this position is valable
      $is_valable = true;

      foreach ($unavailable_positions as $_position) if ($offset_start == $_position) {
        $is_valable = false;
        break;
      }

      // If not valable, we add the offset to skip this part and redo a search
      if (!$is_valable) {
        $offset += strlen($match[0][0]);
        $result = preg_match("/$uncompiled_regex/" . $mode, $uncompiled_content, $match, PREG_OFFSET_CAPTURE, $offset);
        continue;
      }

      // Sets the vars
      $vars = $match;

      // Removes the first element
      unset($vars[0]);

      // Do this because it is offset capture and we remove the offset from the table
      array_walk($vars, function (&$e) {
        $e = $e[0];
      });

      // Repair the keys
      $vars = array_values($vars);

      // Get the new syntax
      $new_syntax = $this->get_compiled_syntax($vars);

      // Replace the old syntax with the new one
      $uncompiled_content = substr_replace($uncompiled_content, $new_syntax, $offset_start, strlen($match[0][0]));

      // Sets the new offset to be position_of_old + strlen of new syntax
      $offset = $offset_start + strlen($new_syntax);

      // Redo the search
      $result = preg_match("/$uncompiled_regex/" . $mode, $uncompiled_content, $match, PREG_OFFSET_CAPTURE, $offset);
    }

    return $uncompiled_content;
  }
}
