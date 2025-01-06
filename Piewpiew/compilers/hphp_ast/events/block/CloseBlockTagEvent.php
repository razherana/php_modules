<?php

namespace Piewpiew\compilers\hphp_ast\events\block;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class CloseBlockTagEvent extends AbstractTermEvent
{
  public function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    if (count($lexiqs) == 1)
      throw new HPHPAstViewException("Missing closing_tag '>' but found nothing");
    if (count($lexiqs) >= 2 && ($lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag"))
      throw new HPHPAstViewException("Missing closing_tag '>' but found '" . ($lexiqs[1]->name ?? "text") . " : "  . $lexiqs[1]->content . "'");

    /** @var HPHPAstCompiler $compiler */
    $compiler = $this->compiler;

    if ($compiler->block <= 0)
      throw new HPHPAstViewException("Closing on no open block on index : " . $this->index);

    $compiler->block--;

    $lexiqs[0]->replace('<?php $___vars___->end_block(); ');
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
