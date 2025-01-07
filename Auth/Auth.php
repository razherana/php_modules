<?php

namespace Auth;

use ConfigReader\ConfigurableElement;
use Auth\exceptions\AuthDefinitionException;
use Auth\exceptions\AuthException;
use Exception;
use Lorm\BaseModel;
use Lorm\queries\maker\queries\DefaultQueryMaker;
use Lorm\queries\maker\request\elements\Where;

class Auth extends ConfigurableElement
{
  /**
   * Contains the name of this Auth
   * @var string $name
   */
  protected $name = '';

  /**
   * Contains the model to use
   * @var string $model
   */
  protected $model = '';

  /**
   * Contains the id_columns
   * @var string[] $id_columns
   */
  protected $id_columns = [];

  /**
   * Contains the pass_columns
   * @var ?string $pass_column
   */
  protected $pass_column = null;

  /**
   * Contains the hash_method
   * @var \Closure $hash_method
   */
  protected $hash_method = null;

  /**
   * Contains the password_verify
   * @var \Closure $password_verify
   */
  protected $password_verify = null;

  /**
   * Contains the closure to get the model in relation with the id
   * @var \Closure $id_relation
   */
  protected $id_relation = null;

  /**
   * Contains cached models after getting them
   * @var array<string, mixed> $cached_models
   */
  protected static $cached_models = [];

  public function config_file(): string
  {
    return AUTH_DIR . DIRECTORY_SEPARATOR . 'config';
  }

  public static function get_session_reserved_keywords(): array
  {
    return [
      (new Auth)->read_cached_config('session_key_name')
    ];
  }

  public static function initialize_session(): void
  {
    $key = (new Auth)->read_cached_config('session_key_name');

    if (!isset($_SESSION[$key]))
      $_SESSION[$key] = [];
  }

  /**
   * Constructs a new Auth
   * @param string $name The auth's name, if $name === "ignore" it skips checkings and settings
   * @param string $model The Model::class, if $model === "ignore" it skips checkings and settings
   * @param string[]|string $id_columns
   * @param ?string $pass_column
   * @param ?\Closure $hash_method
   * @param ?\Closure $password_verify
   * @param ?\Closure $id_relation
   * @return self
   */
  public function __construct($name = "ignore", $model = "ignore", $id_columns = null, $pass_column = null, $hash_method = null, $password_verify = null, $id_relation = null)
  {
    if ($model === "ignore" || $name === "ignore") return;

    if (!is_a($model, BaseModel::class, true))
      throw new AuthDefinitionException("This class is not a model : " . $model, $this);

    $this->name = $name;
    $this->model = $model;

    if (is_string($id_columns))
      $id_columns = [$id_columns];

    $this->id_columns = $id_columns;
    $this->pass_column = $pass_column;
    $this->hash_method = $hash_method;
    $this->password_verify = $password_verify;
    $this->id_relation = $id_relation;

    if ($this->id_relation === null)
      $this->id_relation = $this->read_cached_config('default_id_relation');

    if ($this->hash_method === null)
      $this->hash_method = $this->read_cached_config('default_hash_method');

    if ($this->password_verify === null)
      $this->password_verify = $this->read_cached_config('default_password_verify');

    if (!isset($_SESSION[(new Auth)->read_cached_config('session_key_name')]))
      $_SESSION[(new Auth)->read_cached_config('session_key_name')] = [];
  }

  /**
   * Get an instance of Auth from a defined definition in config
   * @param string $name_of_auth
   * @return self The Auth instance
   */
  public static function from_config($name_of_auth = 0): self
  {
    $auths = (new Auth)->read_cached_config('auths');

    if (is_int($name_of_auth))
      if (!isset(array_keys($auths)[$name_of_auth]))
        throw new AuthDefinitionException("This auth doesn't exist in config : numero $name_of_auth");
      else $name_of_auth = array_keys($auths)[$name_of_auth];
    elseif (!isset($auths[$name_of_auth]))
      throw new AuthDefinitionException("This auth doesn't exist in config : '$name_of_auth'");

    $config = $auths[$name_of_auth];

    return new Auth($name_of_auth, ...$config);
  }

  // Auth functions starts here

  /**
   * Login using datas
   * @param array{id_column: array<string, string>, pass_column: string} $data
   */
  public function login($data): bool
  {
    $id = $data['id_column'];

    $id_columns_and = $this->id_columns;

    foreach ($id_columns_and as $_id) if (!isset($id[$_id]))
      throw new AuthException("The id_column passed for Auth doesn't have the obligatory id : '$_id'", $this);

    $obligatory_id = array_filter($id, function ($k) use ($id_columns_and) {
      return in_array($k, $id_columns_and);
    }, ARRAY_FILTER_USE_KEY);

    $non_obligatory_id = array_filter($id, function ($k) use ($id_columns_and) {
      return !in_array($k, $id_columns_and);
    }, ARRAY_FILTER_USE_KEY);

    $pass_column = $this->pass_column;

    $model = $this->model;

    /** @var BaseModel $query */
    $query = new $model();

    $query = $query::doquery();

    // Repair obligatory_id to match where_all
    $ob_id = [];
    foreach ($obligatory_id as $k => $v)
      $ob_id[] = [$k, $v];

    $non_ob_id = [];
    foreach ($non_obligatory_id as $k => $v)
      $non_ob_id[] = [$k, $v];

    // Add the wheres in query
    $query->where_all($ob_id);

    // We make it into group if multiple non obligatory id columns
    if (count($non_obligatory_id) > 1)
      $query->or_group_where(function () use ($non_ob_id) {
        /** @var DefaultQueryMaker $this */
        $this->where_all($non_ob_id, Where::OR);
      });
    else if (count($non_obligatory_id) == 1)
      $query->or_where($_k = array_keys($non_obligatory_id)[0], '=', $non_obligatory_id[$_k]);

    /** @var BaseModel[] $result */
    $result = $query->get();

    if (count($result) != 1)
      return false;

    $result = $result[array_keys($result)[0]];

    if (is_null($pass_column))
      return $this->save($result);

    try {
      $result->{$pass_column};
    } catch (Exception $e) {
      if($e->getMessage() == "No column $pass_column")
      throw new AuthException("The pass_column : '$pass_column' doesn't exist in the model given -> " . var_export($result, true), $this);
    }

    $hashed_password = $result->{$pass_column};
    $password = $data['pass_column'];

    if (($this->password_verify)($password, $hashed_password))
      return $this->save($result);

    return false;
  }

  /**
   * Removes the login of the auth
   */
  public function logout(): void
  {
    $auth = $_SESSION[$this->read_cached_config("session_key_name")];
    unset($auth[$this->name]);

    $_SESSION[$this->read_cached_config("session_key_name")] = $auth;
  }

  /**
   * Register a new element with password hashed
   * @param array<string, string> $data
   */
  public function register($data): bool
  {
    // Hash the pass column
    if (!is_null($this->pass_column)) {
      if (!isset($data[$this->pass_column]))
        throw new AuthException("The data given in register doesn't have a pass column, but the pass column is required", $this);

      $data[$this->pass_column] = ($this->hash_method)($data[$this->pass_column]);
    }

    return ($this->model)::create($data);
  }

  /**
   * Force login with an id
   * @param mixed $id
   */
  public function force_login_id($id): void
  {
    $auth = $_SESSION[$this->read_cached_config("session_key_name")];
    $auth[$this->name] = $id;

    $_SESSION[$this->read_cached_config("session_key_name")] = $auth;
  }

  /**
   * Force login with id_columns
   * @param array<string, string> $id_columns
   */
  public function force_login($id_columns): bool
  {
    $id = $id_columns;

    $id_columns_and = $this->id_columns;

    foreach ($id_columns_and as $_id) if (!isset($id[$_id]))
      throw new AuthException("The id_column passed for Auth doesn't have the obligatory id : '$_id'", $this);

    $obligatory_id = array_filter($id, function ($k) use ($id_columns_and) {
      return in_array($k, $id_columns_and);
    }, ARRAY_FILTER_USE_KEY);

    $non_obligatory_id = array_filter($id, function ($k) use ($id_columns_and) {
      return !in_array($k, $id_columns_and);
    }, ARRAY_FILTER_USE_KEY);

    $model = $this->model;

    /** @var BaseModel $query */
    $query = new $model(true, true, false);
    $query = $query::doquery();

    // Add the wheres in query
    if (empty($non_obligatory_id)) {
      $query->where_all($obligatory_id);
    } else {
      $query
        ->where_all($non_obligatory_id, Where::OR)
        ->and_group_where(function () use ($obligatory_id) {
          /** @var DefaultQueryMaker $this */
          $this->where_all($obligatory_id);
        });
    }

    /** @var BaseModel[] $result */
    $result = $query->get();

    if (count($result) != 1)
      return false;

    $result = $result[array_keys($result)[0]];

    return $this->save($result);
  }

  /**
   * Checks if loggedin on this auth
   * @return bool
   */
  public function loggedin(): bool
  {
    $auth = $_SESSION[$this->read_cached_config("session_key_name")];
    return in_array($this->name, array_keys($auth));
  }

  /**
   * Get the model instance in relation to the id saved
   * If not logged in, this throws an exception
   * @return mixed The model
   */
  public function get(): mixed
  {
    if (!$this->loggedin())
      throw new AuthException("Cannot get the model because there is no loggedin");

    // If already cached then send the cached one
    if (in_array($this->name, array_keys($this::$cached_models)))
      return $this::$cached_models[$this->name];

    // Sets and returns the model
    return ($this::$cached_models[$this->name] = ($this->id_relation)->call($this, $this->id()));
  }

  /**
   * Get the id saved inside session
   * Throws an exception if not loggedin
   * @return mixed The id
   */
  public function id(): mixed
  {
    if (!$this->loggedin())
      throw new AuthException("Cannot get the model because there is no loggedin");

    return $_SESSION[$this->read_cached_config("session_key_name")][$this->name];
  }

  /**
   * Saves the $model into the auth session
   * @param BaseModel $model The authenticated model
   * @return bool
   */
  protected function save($model) : bool
  {
    $key = $model->{$model->primary_key};

    $auth = $_SESSION[$this->read_cached_config("session_key_name")];
    $auth[$this->name] = $key;

    $_SESSION[$this->read_cached_config("session_key_name")] = $auth;
    return true;
  }
}
