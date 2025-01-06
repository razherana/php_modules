<?php

namespace Piewpiew\compilers\hphp_ast\events\templates;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class CloseTemplateTagEvent extends AbstractTermEvent
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

    if ($compiler->template <= 0)
      throw new HPHPAstViewException("Closing on no open template on index : " . $this->index);

    $compiler->template--;

    $lexiqs[1]->replace("");
  }

  public function return_lexiqs(): array
  {
    $this->handle();
    return $this->lexiqs;
  }

  public function return_skips(): int
  {
    return 1;
  }
}
