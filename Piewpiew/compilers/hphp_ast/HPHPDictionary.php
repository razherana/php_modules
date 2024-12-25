<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\hphp_ast\events\if\CloseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenElseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenIfTagEvent;
use Piewpiew\view\compiler\ast\AbstractDictionary;
use Piewpiew\view\compiler\ast\Lexiq;

class HPHPDictionary extends AbstractDictionary
{
  public function get_lexiqs(): array
  {
    return [
      // Global
      "vars" => "vars\s*=\"(.*?)\"\s*",
      "closing_tag" => "\>",

      // If conditions
      "open_if" => "\<\s*if(?:\s+content\s*=\s*\"(.*?)\")?\s*",
      "close_if" => "\<\/\s*if(?:\s+(\w+)\s*)?",
      "open_elseif" => "\<\s*elseif(?:\s+content\s*=\s*\"(.*?)\")?\s*",
      "close_elseif" => "\<\/\s*elseif(?:\s+(\w+)\s*)?",
      "open_else" => "\<\s*else\s*",
      "close_else" => "\<\/\s*else\s*",
    ];
  }

  public function get_events(): array
  {
    return [
      OpenIfTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name === "open_if",
        "open_tag_elseif" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name === "open_elseif",
      ],
      CloseIfTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["close_if", "close_elseif", "close_else"]),
      ],
    ];
  }
}
