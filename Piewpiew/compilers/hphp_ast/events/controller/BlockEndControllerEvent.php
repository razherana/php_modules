<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class BlockEndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    self::checkLexiqConsistency($this->lexiqs);
  }

  /** @param (TextLexiq|Lexiq)[] $lexiqs */
  private static function checkLexiqConsistency(array $lexiqs)
  {
    $current = null;

    foreach ($lexiqs as $lexiq) {
      if ($lexiq instanceof TextLexiq) continue;

      $tag = $lexiq->name;
      $position = $lexiq->position;

      if ($tag === 'open_block') {
        if ($current !== null)
          throw new HPHPAstViewException("Blocks can't be nested in $position");

        $name = $lexiq->matches[1] ?? false;
        if ($name === false)
          throw new HPHPAstViewException("The block in $position doesn't have a name, shouldn't happen...\nError in regex maybe?");

        $current = [
          'lexiq' => $lexiq,
          'name' => $name,
        ];
      } elseif ($tag == 'close_block') {
        if ($current === null)
          throw new HPHPAstViewException("No blocks started, cannot close in $position");
        $current = null;
      }
    }

    if (!empty($current)) {
      throw new HPHPAstViewException("Unclosed 'block' with name '{$current['name']}' tag at position {$current['lexiq']->position}.");
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
