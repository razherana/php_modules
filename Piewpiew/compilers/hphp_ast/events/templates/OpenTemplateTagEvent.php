<?php

namespace Piewpiew\compilers\hphp_ast\events\templates;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class OpenTemplateTagEvent extends AbstractTermEvent
{
  private function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    if (count($lexiqs) < 2 || $lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag")
      throw new HPHPAstViewException("Missing closing_tag '>' in block tag lexiq no : " . $this->index);

    if ((!isset($lexiqs[0]->matches[1]) || ($name = trim($lexiqs[0]->matches[1] ?? "")) == ""))
      throw new HPHPAstViewException("Missing name in template tag lexiq no : " . $this->index);

    /** @var HPHPAstCompiler $compiler */
    $compiler = $this->compiler;
    $compiler->template++;

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
