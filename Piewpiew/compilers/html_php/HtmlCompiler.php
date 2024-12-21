<?php

namespace Piewpiew\compilers\html_php;

use Piewpiew\compilers\html_php\components\HtmlAuth;
use Piewpiew\compilers\html_php\components\HtmlBlock;
use Piewpiew\compilers\html_php\components\HtmlComment;
use Piewpiew\compilers\html_php\components\HtmlElse;
use Piewpiew\compilers\html_php\components\HtmlElseIf;
use Piewpiew\compilers\html_php\components\HtmlEndBlock;
use Piewpiew\compilers\html_php\components\HtmlEndElse;
use Piewpiew\compilers\html_php\components\HtmlEndFor;
use Piewpiew\compilers\html_php\components\HtmlEndIf;
use Piewpiew\compilers\html_php\components\HtmlFor;
use Piewpiew\compilers\html_php\components\HtmlGuest;
use Piewpiew\compilers\html_php\components\HtmlIf;
use Piewpiew\compilers\html_php\components\HtmlInclude;
use Piewpiew\compilers\html_php\components\HtmlJoin;
use Piewpiew\compilers\html_php\components\HtmlTemplate;
use Piewpiew\compilers\html_php\components\HtmlUse;
use Piewpiew\compilers\html_php\components\HtmlUseTemplate;
use Piewpiew\view\compiler\AbstractCompiler;

class HtmlCompiler extends AbstractCompiler
{
  protected function get_compiler_name(): string
  {
    return "html_php";
  }

  protected function get_extensions(): array
  {
    return ["hphp"];
  }

  protected function get_components(): array
  {
    return [
      HtmlComment::class,
      HtmlIf::class,
      HtmlEndIf::class,
      HtmlAuth::class,
      HtmlGuest::class,
      HtmlElseIf::class,
      HtmlElse::class,
      HtmlEndElse::class,
      HtmlFor::class,
      HtmlEndFor::class,
      HtmlUse::class,
      HtmlUseTemplate::class,
      HtmlInclude::class,
      HtmlJoin::class,
      HtmlBlock::class,
      HtmlEndBlock::class,
      HtmlTemplate::class,
    ];
  }

  public function get_view_var_class(): string
  {
    return HtmlViewVars::class;
  }
}
