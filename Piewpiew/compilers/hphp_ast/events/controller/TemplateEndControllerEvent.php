<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\compilers\hphp_ast\exceptions\HPHPAstViewException;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;

class TemplateEndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    self::checkLexiqConsistency($this->lexiqs);
  }

  /** @param (TextLexiq|Lexiq)[] $lexiqs */
  private static function checkLexiqConsistency(&$lexiqs)
  {
    $current = null;
    $curr_lexiqs = "";
    $remove = [];

    foreach ($lexiqs as $i => $lexiq) {
      $tag = $lexiq->name ?? "";
      $position = $lexiq->position;

      if ($tag === 'open_template') {
        if ($current !== null)
          throw new HPHPAstViewException("Templates can't be nested in $position");

        $name = $lexiq->matches[1] ?? false;
        if ($name === false)
          throw new HPHPAstViewException("The template in $position doesn't have a name, shouldn't happen...\nError in regex maybe?");

        $current = [
          'lexiq' => $lexiq,
          'name' => $name,
        ];
      } elseif ($tag == 'close_template') {
        if ($current === null)
          throw new HPHPAstViewException("No template started, cannot close in $position");

        $lexiq->replace('');

        $uses = trim($current['lexiq']->matches[2] ?? "[]");
        $current['lexiq']->replace("<?php \$___vars___->add_template('{$current['name']}', " . var_export($curr_lexiqs, true) . ", $uses) ?>");
        $current = null;
        $curr_lexiqs = "";
      } elseif ($current !== null) {
        $curr_lexiqs .= $lexiq->content;
        $remove[] = $i;
      }
    }

    foreach($remove as $i)
      unset($lexiqs[$i]);

    $lexiqs = array_values($lexiqs);

    if (!empty($current))
      throw new HPHPAstViewException("Unclosed 'template' with name '{$current['name']}' tag at position {$current['lexiq']->position}.");
  }

  public function return_lexiqs(): array
  {
    $this->handle();
    return $this->lexiqs;
  }

  public function return_skips(): int
  {
    return count($this->lexiqs);
  }
}
