<?php

namespace Piewpiew\compilers\hphp_ast\events\loop;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class CloseLoopTagEvent extends AbstractTermEvent
{
  public function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    if (count($lexiqs) == 1)
      throw new HPHPAstViewException("Missing closing_tag '>' but found nothing");
    if (count($lexiqs) >= 2 && ($lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag"))
      throw new HPHPAstViewException("Missing closing_tag '>' but found '" . ($lexiqs[1]->name ?? "text") . " : "  . $lexiqs[1]->content . "'");

    $type = $lexiqs[0]->matches[1];

    /** @var HPHPAstCompiler $compiler */
    $compiler = $this->compiler;

    if (($compiler->loop_nest[$type] ?? 0) <= 0)
      throw new HPHPAstViewException("Closing on $type loop on index : " . $this->index);

    $compiler->loop_nest[$type]--;

    $lexiqs[0]->replace("<?php end$type; ");
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
