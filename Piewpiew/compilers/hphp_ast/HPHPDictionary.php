<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\hphp_ast\events\controller\EndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\IfEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\LoopEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\if\CloseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenElseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\loop\CloseLoopTagEvent;
use Piewpiew\compilers\hphp_ast\events\loop\OpenLoopTagEvent;
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
      "open_if" => "\<if(?:\s+content\s*=\s*\"(.*?)\")?\s*",
      "close_if" => "\<\/if(?:\s+(\w+)\s*)?\s*",
      "open_elseif" => "\<elseif(?:\s+content\s*=\s*\"(.*?)\")?\s*",
      "close_elseif" => "\<\/elseif(?:\s+(\w+)\s*)?\s*",
      "open_else" => "\<else\s*",
      "close_else" => "\<\/else\s*",

      // Loops
      "open_loop" => "\<(foreach|for|while)\s+loop\s*=\s*\"(.*?)\"\s*",
      "close_loop" => "\<\/(foreach|for|while)\s*",

      // Block
      "open_block" => "\<block\s+(\w+)\s*",
      "close_block" => "\<\/block\s*\>"
    ];
  }

  public function get_events(): array
  {
    $end_check = fn($lexiqs, $index) => ($s = count($lexiqs)) > 0 && $s == $index + 1;
    return [
      OpenIfTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name === "open_if",
        "open_tag_elseif" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name === "open_elseif",
        "open_tag_else" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name === "open_else"
      ],
      CloseIfTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["close_if", "close_elseif", "close_else"]),
      ],
      OpenLoopTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["open_loop"]),
      ],
      CloseLoopTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["close_loop"]),
      ],

      // End checking controller
      EndControllerEvent::class => [
        // End checking event classes
        // for example : EndControllerEvent::class => $end_check,
        IfEndControllerEvent::class => $end_check,
        LoopEndControllerEvent::class => $end_check,
      ]
    ];
  }
}
