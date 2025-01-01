<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class JoinEndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    self::checkLexiqConsistency($this->lexiqs);
  }

  /** @param (TextLexiq|Lexiq)[] $lexiqs */
  private static function checkLexiqConsistency(&$lexiqs)
  {
    $open = 0;
    $join = null;

    foreach ($lexiqs as $lexiq) {
      if ($lexiq instanceof TextLexiq) continue;

      if ($lexiq->name == "join")
        $join = $lexiq;
      elseif ($lexiq->name == "open_php")
        $open++;
      elseif ($lexiq->name == "close_php")
        $open--;
    }

    if ($join === null)
      return;

    $use_join_syntax = '<?php $___vars___->use_join(); ?>';

    if ($open > 0)
      $use_join_syntax = "?>$use_join_syntax";

    $pos = 0;
    if (!empty($lexiqs))
      $pos = ($c = end($lexiqs))->position + strlen($c->content);
    $lexiqs[] = new TextLexiq($use_join_syntax, $pos);
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
