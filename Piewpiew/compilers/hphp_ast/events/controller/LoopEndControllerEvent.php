<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class LoopEndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    self::checkLexiqConsistency($this->lexiqs);
  }

  /** @param (TextLexiq|Lexiq)[] $lexiqs */
  private static function checkLexiqConsistency(array $lexiqs)
  {
    $stack = [];

    foreach ($lexiqs as $lexiq) {
      if ($lexiq instanceof TextLexiq) continue;

      $tag = $lexiq->name;
      $position = $lexiq->position;

      if ($tag === 'open_loop') {
        $stack[] = [
          'lexiq' => $lexiq,
          'type' => $lexiq->matches[1],
        ];
      } elseif ($tag == 'close_loop') {
        $currentBlock = &$stack[count($stack) - 1] ?? false;
        if (empty($stack) || $currentBlock['type'] !== $lexiq->matches[1])
          throw new HPHPAstViewException("Unexpected closing '{$lexiq->matches[1]}' tag at position $position without a matching opening '{$currentBlock['type']}'.");
        array_pop($stack);
      } elseif ($tag == 'continue' || $tag == 'break') {
        if (empty($stack))
          throw new HPHPAstViewException("Unexpected '$tag' tag not in a loop.");
        $count = $lexiq->matches[1] ?? 1;
        if (count($stack) < $count)
          throw new HPHPAstViewException("Cannot $tag $count level(s).");
        $lexiq->replace("<?php $tag $count; ?>");
      }
    }

    if (!empty($stack)) {
      $unclosedBlock = $stack[0];
      throw new HPHPAstViewException("Unclosed '{$unclosedBlock['lexiq']->name}' tag at position {$unclosedBlock['lexiq']->position}.");
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
