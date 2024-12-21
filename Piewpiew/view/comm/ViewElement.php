<?php

namespace Piewpiew\view\comm;

/**
 * This class represents the view
 */
class ViewElement
{
  /**
   * Contains the file dir.file
   * @var string $view_name
   */
  public $view_name = "";

  /**
   * Contains the content of the view
   * @var string $content
   */
  public $content = "";

  /**
   * Contains the view vars
   * @var ViewVars $vars
   */
  public $vars = null;

  public function __construct($view_name = "")
  {
    $this->view_name = $view_name;
    $this->vars = new ViewVars(['___view___' => $this]);
  }
}
