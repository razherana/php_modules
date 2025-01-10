<?php

namespace Piewpiew\compilers\hphp_ast;

use Piewpiew\compilers\hphp_ast\events\block\CloseBlockTagEvent;
use Piewpiew\compilers\hphp_ast\events\block\OpenBlockTagEvent;
use Piewpiew\compilers\hphp_ast\events\controller\BlockEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\EndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\IfEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\JoinEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\LoopEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\controller\TemplateEndControllerEvent;
use Piewpiew\compilers\hphp_ast\events\if\CloseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenElseIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\if\OpenIfTagEvent;
use Piewpiew\compilers\hphp_ast\events\loop\CloseLoopTagEvent;
use Piewpiew\compilers\hphp_ast\events\loop\OpenLoopTagEvent;
use Piewpiew\compilers\hphp_ast\events\orphan\IncludeTagEvent;
use Piewpiew\compilers\hphp_ast\events\orphan\JoinTagEvent;
use Piewpiew\compilers\hphp_ast\events\orphan\UseTagEvent;
use Piewpiew\compilers\hphp_ast\events\orphan\UseTemplateTagEvent;
use Piewpiew\compilers\hphp_ast\events\templates\CloseTemplateTagEvent;
use Piewpiew\compilers\hphp_ast\events\templates\OpenTemplateTagEvent;
use Piewpiew\view\compiler\ast\AbstractDictionary;
use Piewpiew\view\compiler\ast\Lexiq;

class HPHPDictionary extends AbstractDictionary
{
  public function get_lexiqs(): array
  {
    return [
      // Global
      "closing_tag" => "\>",
      "open_php" => "\<\?php",
      "close_php" => "\?\>",
      "close_form" => "\<\/form",

      // If conditions
      "open_if" => "\<if(?:\s+condition\s*=\s*\"(.*?)\")?\s*",
      "close_if" => "\<\/if(?:\s+(\w+)\s*)?\s*",
      "open_elseif" => "\<elseif(?:\s+condition\s*=\s*\"(.*?)\"\s*)?\s*",
      "close_elseif" => "\<\/elseif(?:\s+(\w+)\s*)?\s*",
      "open_else" => "\<else\s*",
      "close_else" => "\<\/else\s*",

      // Loops
      "open_loop" => "\<(foreach|for|while)\s+loop\s*=\s*\"(.*?)\"\s*",
      "close_loop" => "\<\/(foreach|for|while)\s*",
      "continue" => "\<continue(?:\s+(\d+))?\s*\/?\s*\>",
      "break" => "\<break(?:\s+(\d+))?\s*\/?\s*\>",

      // Block
      "open_block" => "\<block\s+(\w+)\s*",
      "close_block" => "\<\/block\s*",

      // Template
      "open_template" => "\<template\s+(\w+)(?:\s+use=\"(.*?)\"\s*)?\s*",
      "close_template" => "\<\/template\s*",

      // Orphans
      "join" => "\<join\s+(\w+)(?:\s+vars=\"(.*?)\"\s*)?\s*\/?\s*",
      "use" => "\<use\s+(\w+)\s*\/?\s*",
      "include" => "\<include\s+(\w+)(?:\s+vars=\"(.*?)\"\s*)?\s*\/?\s*",
      "use-template" => "<use-template\s+(\w+)(?:\s+vars=\"(.*?)\"\s*)?\/?\s*",
    ];
  }

  public function get_events(): array
  {
    $end_check = fn($lexiqs, $index) => ($s = count($lexiqs)) > 0 && $s == $index + 1;
    return [
      // If cond

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

      // Loops

      OpenLoopTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["open_loop"]),
      ],

      CloseLoopTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["close_loop"]),
      ],

      // Blocks

      OpenBlockTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["open_block"]),
      ],

      CloseBlockTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["close_block"]),
      ],

      // Templates

      OpenTemplateTagEvent::class => [
        "open_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["open_template"]),
      ],

      CloseTemplateTagEvent::class => [
        "close_tag" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && $lexiqs[$index]->name == "close_template",
      ],

      // Orphan tags

      JoinTagEvent::class => [
        "join" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["join"]),
      ],

      UseTagEvent::class => [
        "use" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["use"]),
      ],

      IncludeTagEvent::class => [
        "include" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["include"]),
      ],

      UseTemplateTagEvent::class => [
        "use-template" => fn($lexiqs, $index) => $lexiqs[$index] instanceof Lexiq
          && in_array($lexiqs[$index]->name, ["use-template"]),
      ],


      // End checking controllers

      EndControllerEvent::class => [
        // End checking event classes
        // for example : EndControllerEvent::class => $end_check,
        IfEndControllerEvent::class => $end_check,
        LoopEndControllerEvent::class => $end_check,
        BlockEndControllerEvent::class => $end_check,
        JoinEndControllerEvent::class => $end_check,
        TemplateEndControllerEvent::class => $end_check
      ]
    ];
  }
}
