<?php

namespace Piewpiew\compilers\hphp_ast\events\loop;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class OpenLoopTagEvent extends AbstractTermEvent
{
  private function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    $type = trim($lexiqs[0]->matches[1]);

    if (count($lexiqs) < 2 || $lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag")
      throw new HPHPAstViewException("Missing closing_tag '>' in $type tag lexiq no : " . $this->index);

    if (!isset($lexiqs[0]->matches[2]) || ($cond = trim($lexiqs[0]->matches[2])) == "")
      throw new HPHPAstViewException("Missing condition in $type tag lexiq no : " . $this->index);

    /** @var HPHPAstCompiler $compiler */
    $compiler = $this->compiler;
    $compiler->loop_index[$type] = $this->index;
    $compiler->loop_nest[$type] = ($compiler->loop_nest[$type] ?? 0) + 1;

    $lexiqs[0]->replace("<?php $type($cond): ");
    $lexiqs[1]->replace("?>");
  }

  public function return_lexiqs(): array
  {
    $this->handle();
    return $this->lexiqs;
  }

  public function return_skips(): int
  {
    return 2;
  }
}
