<?php

namespace Piewpiew\compilers\hphp_ast\events\if;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\TextLexiq;

class OpenIfTagEvent extends AbstractTermEvent
{
  private function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);

    $type = "if";
    if($this->name == "open_tag_elseif")  
      $type = "elseif";

    if (count($lexiqs) < 2 || $lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag")
      throw new HPHPAstViewException("Missing closing_tag '>' in $type tag lexiq no : " . $this->index);

    if (!isset($lexiqs[0]->matches[1]) || ($cond = trim($lexiqs[0]->matches[1])) == "")
      throw new HPHPAstViewException("Missing condition in $type tag lexiq no : " . $this->index);

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
