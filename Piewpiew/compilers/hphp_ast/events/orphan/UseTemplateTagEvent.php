<?php

namespace Piewpiew\compilers\hphp_ast\events\orphan;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class UseTemplateTagEvent extends AbstractTermEvent
{
  private function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    $name = trim($lexiqs[0]->matches[1]) ?? "";

    if (count($lexiqs) < 2 || $lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag")
      throw new HPHPAstViewException("Missing closing_tag '>' in use-template tag lexiq no : " . $this->index);

    if (!isset($lexiqs[0]->matches[1]) || $name == "")
      throw new HPHPAstViewException("Missing name in use-template tag lexiq no : " . $this->index);

    $vars = $lexiqs[0]->matches[2] ?? "[]";

    $lexiqs[0]->replace("<?php \$___vars___->use_template('$name', $vars); ");
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
