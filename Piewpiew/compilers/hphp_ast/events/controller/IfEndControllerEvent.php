<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class IfEndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    self::checkLexiqConsistency($this->lexiqs);
  }

  /** @param (TextLexiq|Lexiq)[] $lexiqs */
  private static function checkLexiqConsistency(array $lexiqs)
  {
    $stack = [];

    foreach ($lexiqs as $index => $lexiq) {
      if ($lexiq instanceof TextLexiq) continue;

      $tag = $lexiq->name; // 'tag' identifies the type
      $position = $lexiq->position;

      if ($tag === 'open_if') {
        $stack[] = [
          'lexiq' => $lexiq,
          "elseif_opened" => false,
          "else_opened" => false,
          "waiting_else" => false,
          "waiting_elseif" => false,
          'hasElse' => false, 
        ];
      } elseif ($tag === 'open_elseif') {
        if (empty($stack)) {
          throw new HPHPAstViewException("Unexpected 'elseif' tag at position $position outside of any 'if' block.");
        } else {
          $currentBlock = &$stack[count($stack) - 1];
          if (!($currentBlock['waiting_elseif'] ?? false))
            throw new HPHPAstViewException("'elseif' tag at position $position cannot appear without a closing 'if elseif' '</if elseif>'.");
          if ($currentBlock['hasElse'])
            throw new HPHPAstViewException("'elseif' tag at position $position cannot appear after 'else' in the same 'if' block.");
          if ($currentBlock["elseif_opened"] === true)
            throw new HPHPAstViewException("'elseif' tag at position $position cannot appear after opening 'elseif' block.");
          $currentBlock['elseif_opened'] = true;
        }
      } elseif ($tag === 'open_else') {
        if (empty($stack))
          throw new HPHPAstViewException("Unexpected 'else' tag at position $position outside of any 'if' block.");
        else {
          $currentBlock = &$stack[count($stack) - 1];
          if (!($currentBlock['waiting_else'] ?? false)) {
            throw new HPHPAstViewException("'else' tag at position $position cannot appear without a closing 'if else' '</if else>'.");
          }
          if ($currentBlock['else_opened'])
            throw new HPHPAstViewException("Unexpected 'else' tag at position $position, need to close the older one.");
          else if ($currentBlock['hasElse'])
            throw new HPHPAstViewException("Duplicate 'else' tag at position $position in the same 'if' block.");
          else {
            $currentBlock['hasElse'] = true;
            $currentBlock['else_opened'] = true;
          }
        }
      } elseif ($tag === 'close_if') {
        if (empty($stack))
          throw new HPHPAstViewException("Unexpected closing 'if' tag at position $position without a matching opening 'if'.");
        $currentBlock = &$stack[count($stack) - 1];
        if (empty($type = ($lexiq->matches[1] ?? "")))
          array_pop($stack);
        else
          $currentBlock["waiting_$type"] = true;
      } elseif ($tag === 'close_elseif') {
        $curr = &$stack[count($stack) - 1] ?? false;
        if (empty($stack) || $curr['elseif_opened'] === false)
          throw new HPHPAstViewException("Unexpected closing 'elseif' tag at position $position without a matching opening.");
        $curr['elseif_opened'] = false;
        if (empty($type = ($lexiq->matches[1] ?? "")))
          array_pop($stack);
        else
          $curr["waiting_$type"] = true;
      } elseif ($tag === 'close_else') {
        $curr = &$stack[count($stack) - 1] ?? false;
        if (empty($stack) || $curr['else_opened'] === false)
          throw new HPHPAstViewException("Unexpected closing 'else' tag at position $position without a matching opening.");
        if ($curr['elseif_opened'] === true)
          throw new HPHPAstViewException("Unexpected closing 'else' tag at position $position with an open elseif.");
        array_pop($stack);
      }
    }

    if (!empty($stack)) {
      $unclosedBlock = $stack[0];
      throw new HPHPAstViewException("Unclosed 'if' tag at position {$unclosedBlock['lexiq']->position}.");
    }
  }

  public function return_lexiqs(): array
  {
    $this->handle();
    return $this->lexiqs;
  }

  public function return_skips(): int
  {
    return count($this->lexiqs);
  }
}
