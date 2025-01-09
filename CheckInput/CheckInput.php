<?php

namespace CheckInput;

use CheckInput\exceptions\ConfigNotFoundException;
use CheckInput\exceptions\UnknownCheckingException;
use Closure;
use ConfigReader\ConfigurableElement;

class CheckInput extends ConfigurableElement
{
  public const ARRAY_ASSOC = 'assoc';
  public const OBJ_ARROW = 'arrow';

  public function config_file(): string
  {
    return CHECK_INPUT_DIR . DIRECTORY_SEPARATOR . "bootstrap_config";
  }

  /**
   * The access_method
   * @var Closure $access_method
   */
  private $access_method;

  /**
   * The data to check
   * @var mixed
   */
  private $data;

  /**
   * The checkers specified per element
   * @var array<string, TestInput[]>
   */
  private $checkers = [];

  /**
   * Contains the string with all the rules
   * @var array<string, string>
   */
  private $rule_strings = [];

  public function __construct($data = [], $access_method = self::ARRAY_ASSOC)
  {
    $this->data = $data;
    $this->access_method = $this->read_cached_config('access')[$access_method] ?? false;

    if ($this->access_method === false)
      throw new ConfigNotFoundException("The access_method of name '{$access_method}' doesn't exist...");
  }

  public function set($element = "", $checkings = "")
  {
    $this->rule_strings[$element] = $checkings;
    return $this;
  }

  private function init_checkings()
  {
    $sep = $this->read_cached_config("separator");

    $checkings = $this->read_cached_config("checking");

    foreach ($this->rule_strings as $element => $string) {
      $splitted = preg_split("/$sep/", $string);
      foreach ($splitted as $rule) {
        preg_match("/([a-zA-Z]+).*/", $rule, $matches);
        $config_name = $matches[1] ?? false;
        $closure = null;
        $regex = null;
        $vars = null;

        if (empty($config_name))
          continue;

        if (!isset($checkings[$config_name]))
          throw new UnknownCheckingException("The rule '$config_name' is undefined");

        if (is_array($array = $checkings[$config_name])) {
          [$closure, $regex] = $array;
        } else {
          $closure = $array;
          $regex = "/" . preg_quote($config_name) . "/";
        }

        /** @var \Closure $closure */

        preg_match($regex, $rule, $vars);

        $this->checkers[$element][] = new TestInput($config_name, $closure, $vars, $regex);
      }
    }
  }

  /**
   * Execute the check
   * @return string|true The message in string or true
   */
  public function check($custom_messages = [])
  {
    $this->checkers = [];
    $this->init_checkings();

    /** @var array<string, Closure> $messages */
    $messages = $this->read_cached_config("messages");

    foreach ($this->checkers as $element => $array_checks) {
      $value = @($this->access_method)($element, $this->data);
      foreach ($array_checks as $check)
        if (!($check->closure)($value, $element, $this->data, $check->vars, $check)) {
          $result = $custom_messages["$element:{$check->name}"] ?? ($messages[$check->name])($value, $element, $this->data, $check->vars, $check);

          // If true, it may be optional or other things
          // We just skip this current element
          if ($result === true)
            break;

          return $result;
        }
    }

    return true;
  }
}
