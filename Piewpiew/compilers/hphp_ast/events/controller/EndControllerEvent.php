<?php

namespace Piewpiew\compilers\hphp_ast\events\controller;

use Piewpiew\view\compiler\ast\AbstractTermEvent;

class EndControllerEvent extends AbstractTermEvent
{
  private function handle()
  {
    $event = new ($this->name)($this->name, $this->dictionary, $this->compiler, fn() => true, 0, $this->lexiqs);
    $this->lexiqs = $event->return_lexiqs();
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
