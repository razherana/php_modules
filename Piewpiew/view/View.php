<?php

namespace Piewpiew\view;

use Piewpiew\view\comm\ViewVars;
use ConfigReader\ConfigurableElement;

class View extends ConfigurableElement
{
  /**
   * Contains all ViewVars instanciated with the key as the view's name
   * @var array<string, ViewVars> $view_vars
   */
  public static $view_vars = [];

  public function config_file(): string
  {
    return PIEWPIEW_DIR . '/config';
  }
}
