<?php

namespace Piewpiew\view\compiler;

use Piewpiew\view\comm\ViewElement;
use Piewpiew\view\compiler\ast\AbstractDictionary;
use Piewpiew\view\compiler\ast\AbstractTermEvent;
use Piewpiew\view\compiler\ast\Lexiq;
use Piewpiew\view\compiler\ast\TextLexiq;
use Piewpiew\view\compiler\exceptions\CompilerException;
use Piewpiew\view\compiler\exceptions\UnsupportedCompilerException;
use Piewpiew\view\View;

abstract class AbstractASTCompiler
{
  /**
   * Contains the ViewElement to compile and return a filename
   * @var ViewElement $view_element
   */
  public $view_element = null;

  /**
   * Contains a View for config reading
   * @var View $view
   */
  public $view;

  /**
   * Contains the dictionary to use
   * @var AbstractDictionary
   */
  protected $dictionary;

  /**
   * @param ViewElement $view_element
   */
  public function __construct($view_element)
  {
    $this->view = new View;
    $this->view_element = $view_element;
  }

  /**
   * Gets the compiler's name
   */
  abstract protected function get_compiler_name(): string;

  /**
   * Gets all of the extension that this compiler compiles
   * @return string[]
   */
  abstract protected function get_extensions(): array;

  /**
   * Get the ViewVars class to use
   */
  abstract public function get_view_var_class(): string;

  /**
   * Reads the content of the file returns it in string
   * @param string $file The view's name
   */
  final protected function read_file($file): string
  {
    $file_without_extension = PIEWPIEW_DIR . DIRECTORY_SEPARATOR . (new View)->read_cached_config('folder') . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $file);

    foreach ($this->get_extensions() as $ext)
      if (is_file($temp_file = "$file_without_extension.$ext")) {
        return file_get_contents($temp_file);
      }

    throw new UnsupportedCompilerException($this);
  }

  /**
   * Parse the full file and divide into lexiqs
   * @param string $contents
   * @return (TextLexiq|Lexiq)[]
   */
  final protected function parse_contents($contents): array
  {
    // Perform dictionary regex search
    $lexiqs = [];
    foreach ($this->dictionary->get_lexiqs() as $lexiq_name => $lexiq_regex) {
      $lexiq_regex = "/$lexiq_regex/";
      if (preg_match_all($lexiq_regex, $contents, $matches, PREG_OFFSET_CAPTURE))
        foreach ($matches[0] as $index => $match) {
          $lexiq_vars = [];

          foreach ($matches as $key => $group) {
            if (!is_int($key) || $key == 0)
              continue;
            $lexiq_vars[$key] = $group[$index][0];
          }

          $lexiqs[] = new Lexiq($lexiq_name, $this, $lexiq_regex, $match[0], $match[1], $lexiq_vars);
        }
    }
    // Sort per position
    usort($lexiqs, fn($a, $b) => $a->position <=> $b->position);

    // Add text in between
    $result = [];
    $lastPos = 0;

    // Foreach every lexiqs and look behind them
    foreach ($lexiqs as $lexiq) {
      $lex_pos = $lexiq->position;

      if ($lex_pos > $lastPos)
        $result[] = new TextLexiq(substr($contents, $lastPos, $lex_pos - $lastPos), $lastPos);

      $result[] = $lexiq;

      // Update lastPos
      $lastPos = $lex_pos + strlen($lexiq->content);
    }

    // If there is some text after the last lexiq
    if ($lastPos < strlen($contents))
      $result[] = new TextLexiq(substr($contents, $lastPos), $lastPos);

    $result = array_values($result);

    // Build tree

    $tree = [];
    $stack = [];

    foreach ($result as $lexiq) {
      while (!empty($stack)) {
        $parent = end($stack);
        $parent_end = $parent->position + strlen($parent->content);

        if (
          $lexiq->position >= $parent->position
          && $lexiq->position + strlen($lexiq->content) <= $parent_end
        ) {
          $parent->children[] = $lexiq;
          $stack[] = $lexiq;
          continue 2;
        } else array_pop($stack);
      }

      $tree[] = $lexiq;
      $stack[] = $lexiq;
    }

    return $tree;
  }

  final protected function run_events($lexiqs): array
  {
    $events = $this->dictionary->get_events();

    foreach ($events as $termEventClass => $event_list)
      foreach ($event_list as $event_name => $condition) {
        $keys = array_keys($lexiqs);
        for ($c = 0; $c < count($keys);) {
          $index = $keys[$c];
          if ($condition instanceof \Closure && !$condition($lexiqs, $index)) {
            $c++;
            continue;
          }
          
          if(is_int($event_name))
            $event_name = $condition;

          /** @var AbstractTermEvent $termEvent */
          $termEvent = new $termEventClass($event_name, $this->dictionary, $this, $condition, $index, $lexiqs);
          $lexiqs = $termEvent->return_lexiqs();
          $c += $termEvent->return_skips();
        }
      }

    return $lexiqs;
  }

  /**
   * @param (TextLexiq|Lexiq)[] $lexiqs
   */
  final protected function concat_lexiqs($lexiqs)
  {
    $content = "";
    foreach ($lexiqs as $l)
      $content .= $l->content;
    return $content;
  }

  /**
   * Compiles the file contents to a normal php file
   * @param string $contents
   */
  final protected function compile($contents): string
  {
    // Add the extract in the first line
    $extract_syntax = "<?php extract(" . View::class . "::\$view_vars['" . $this->view_element->view_name . "']->get_data()); ?>";

    $lexiqs = $this->parse_contents($contents);
    $lexiqs = $this->run_events($lexiqs);
    $contents = $this->concat_lexiqs($lexiqs);

    // Add it in the contents at the end to be wary of errors
    return $extract_syntax . $contents;
  }

  /**
   * Write the file map
   * @param string $file
   * @param string $compiled_contents
   * @return string The compiled filename
   */
  final protected function write_map($file): string
  {
    // Use to get view configs
    $view = $this->view;

    $uniq_compiled_filename = uniqid($this->get_compiler_name() . "_compiled_", true) . ".php";

    // Checks if the map directory is set
    // If not create it
    if (!is_dir($full_map_dir = PIEWPIEW_DIR . DIRECTORY_SEPARATOR . trim($view->read_cached_config('map'), '/\\')))
      if (!mkdir($full_map_dir, 0777, true))
        throw new CompilerException("Unable to create the mapping directory");

    // Checks if the map for the compiler exist
    // If not create it
    $full_map = $full_map_dir . DIRECTORY_SEPARATOR . $this->get_compiler_name() . ".json";

    // Creates the default array
    $json = [];

    // If an old map exists override the default array
    if (is_file($full_map))
      $json = json_decode(file_get_contents($full_map), true);

    // Add the new map
    $json += [$file => $uniq_compiled_filename];

    $temp_file = fopen($full_map, "w");

    if (!$temp_file)
      throw new CompilerException("Unable to create the map file");

    fwrite($temp_file, json_encode($json));

    fclose($temp_file);

    return $uniq_compiled_filename;
  }

  /**
   * Writes the compiled file after registering to map
   * @param string $uniq_id The name registered in the map
   * @param string $contents The content of the new compiled view
   */
  final protected function write_compiled($uniq_id, $contents): void
  {
    // Use to get view configs
    $view = $this->view;

    // Initializes the dir for compiled views
    if (!is_dir($full_compiled_dir = PIEWPIEW_DIR . DIRECTORY_SEPARATOR . trim($view->read_cached_config('compiled'), '\\/')))
      if (!mkdir($full_compiled_dir, 0777, true))
        throw new CompilerException("Unable to create the compiled views directory");

    $file = fopen($full_compiled_dir . DIRECTORY_SEPARATOR . $uniq_id, 'w');

    if (!$file)
      throw new CompilerException("Unable to create the compiled view file");

    fwrite($file, $contents);
    fclose($file);
  }

  /**
   * Get the json map of this compilator.
   * If it doesn't exist, it ___throws___ an error
   */
  final public function get_json_map(): string
  {
    $view = $this->view;
    return PIEWPIEW_DIR . DIRECTORY_SEPARATOR . trim($view->read_cached_config('map'), '/\\') . DIRECTORY_SEPARATOR . $this->get_compiler_name() . ".json";
  }

  /**
   * Get the compiled folder
   */
  final public function get_compiled_folder(): string
  {
    return PIEWPIEW_DIR . DIRECTORY_SEPARATOR . trim($this->view->read_cached_config('compiled'), '\\/');
  }

  /**
   * Compiles (if not compiled) and adds the content to the ViewElement
   */
  final public function compile_and_save_content()
  {
    $view = $this->view;

    $file_exist = is_file($json_path = $this->get_json_map());

    $view_name = $this->view_element->view_name;

    $json_map_file_not_exist = (
      // Or if the file doesn't exist
      !$file_exist

      // Or if the file exist, we decode the json inside and it doesn't have the view
      || !isset(json_decode(file_get_contents($json_path), true)[$view_name])
    );

    // If we always compile or the
    if (
      $view->read_cached_config('always_compile') || $json_map_file_not_exist
    ) {
      $content = $this->read_file($view_name);
      $content = $this->compile($content);

      // If the file doesn't exist, then we create a map
      if ($json_map_file_not_exist)
        $uniq_id = $this->write_map($view_name);

      // Else we just take the value of the existant one
      else
        $uniq_id = json_decode(file_get_contents($json_path), true)[$view_name];


      // Write the file
      $this->write_compiled($uniq_id, $content);
    }

    $map = json_decode(
      file_get_contents($json_path) ?? "[]",
      true
    );

    ob_start();
    include $this->get_compiled_folder() . DIRECTORY_SEPARATOR . $map[$view_name];
    $content = ob_get_clean();

    $this->view_element->content = $content;
  }
}
