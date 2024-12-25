<?php

namespace Piewpiew\view\compiler\ast;

abstract class AbstractDictionary {
  /**
   * This method returns every term that should be used by the compiler,
   * every other text not in this are __text__ value. 
   * @return array<string, string> Array of regex strings. ["lexiq_name" => "regex"]
   */
  abstract public function get_lexiqs() : array;

  /**
   * Get the events to react to : 
   * [TermEvent::class => ["event_name" => $callable($lexiqs, $index), ...]]
   * @return array<string,array<string,\Closure>>
   */
  abstract public function get_events() : array;
}