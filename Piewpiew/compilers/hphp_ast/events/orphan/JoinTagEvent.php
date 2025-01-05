<?php

namespace Piewpiew\compilers\hphp_ast\events\orphan;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\compilers\hphp_ast\HPHPAstCompiler;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class JoinTagEvent extends AbstractTermEvent
{
  private function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    $name = trim($lexiqs[0]->matches[1]) ?? "";

    if (count($lexiqs) < 2 || $lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag")
      throw new HPHPAstViewException("Missing closing_tag '>' in join tag lexiq no : " . $this->index);

    if (!isset($lexiqs[0]->matches[1]) || $name == "")
      throw new HPHPAstViewException("Missing name in join tag lexiq no : " . $this->index);

    /** @var HPHPAstCompiler $compiler */
    $compiler = $this->compiler;

    if ($compiler->join)
      throw new HPHPAstViewException("Cannot join multiple times, in join tag lexiq no : " . $this->index);

    $compiler->join = true;

    $variables = trim($lexiqs[0]->matches[2]) ?? "[]";

    $name = str_replace(".", "/", $name);

    $lexiqs[0]->replace("<?php \$___vars___->join('$name', $variables); ");
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
