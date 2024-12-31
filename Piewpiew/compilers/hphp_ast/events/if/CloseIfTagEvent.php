<?php

namespace Piewpiew\compilers\hphp_ast\events\if;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class CloseIfTagEvent extends AbstractTermEvent
{
  public function handle()
  {
    $lexiqs = array_slice($this->lexiqs, $this->index);
    $endif = $lexiqs[0];

    if (count($lexiqs) == 1)
      throw new HPHPAstViewException("Missing closing_tag '>' but found nothing");
    if (count($lexiqs) >= 2 && ($lexiqs[1] instanceof TextLexiq || $lexiqs[1]->name != "closing_tag"))
      throw new HPHPAstViewException("Missing closing_tag '>' but found '" . $lexiqs[1]->name . " : "  . $lexiqs[1]->content . "'");

    if (!empty($endif_type = ($endif->matches[1] ?? ""))) {
      if (!in_array($endif_type, ["elseif", "else"]))
        throw new HPHPAstViewException("Unknown if modifier $endif_type, should be elseif or else");
      if (count($lexiqs) <= 2)
        throw new HPHPAstViewException("Missing open_$endif_type tag after closing");
      if ($lexiqs[2] instanceof TextLexiq) {
        if (trim($lexiqs[2]->content) != "")
          throw new HPHPAstViewException("Found text after closing_if_$endif_type tag");
        $lexiqs[2]->replace("");
        if (!($lexiqs[3] instanceof Lexiq) || ($type = $lexiqs[3]->name) != "open_$endif_type")
          throw new HPHPAstViewException("Expecting open_$endif_type but found $type");
      } else if (!($lexiqs[2] instanceof Lexiq) || ($type = $lexiqs[2]->name) != "open_$endif_type")
        throw new HPHPAstViewException("Expecting open_$endif_type but found $type");
      $lexiqs[0]->replace("");
    } else
      $this->lexiqs[$this->index]->replace("<?php endif; ?>");
    $lexiqs[1]->replace("");
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
