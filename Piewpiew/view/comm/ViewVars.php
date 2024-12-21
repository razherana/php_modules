<?php

namespace Piewpiew\view\comm;

use Piewpiew\view\compiler\components\Component;

class ViewVars
{
  /**
   * Contains the data for the view
   * name_of_var => value
   * @var array<string, mixed> $data
   */
  protected $data = [];

  /**
   * Contains the array of elements
   * @var array<string, array<string, object>> $elements
   */
  public $elements = [];

  /** 
   * @param array<string, mixed>|static $data
   * @param array<string, array<string, object>> $elements
   */
  public function __construct($data = [], $elements = [])
  {
    // Make it able to be constructed from himself
    if ($data instanceof ViewVars) {
      $elements = $data->elements;
      $data = $data->data;
      unset($data['___vars___']);
    }

    $this->data = $data;
    $this->elements = $elements;

    if (!isset($this->data['___vars___']))
      $this->data += ['___vars___' => $this];
  }

  /**
   * Get all the data
   * @return array<string, mixed>
   */
  public function get_data()
  {
    return $this->data;
  }
}
