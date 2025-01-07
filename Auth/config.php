<?php

/**
 * Contains auth config
 */

return [
  /**
   * Contains all of the defined auths
   * name_of_auth => [config_of_auth]
   */
  "auths" => [
    // "name_of_auth" => [Model::class, 'id_column'|['id_column1', 'id_column2', ...], 'pass_column', ?\Closure hash_method, ?\Closure password_verify, ?\Closure id_relation],
  ],

  /**
   * This defines the default hash method
   * @param string $password
   * @return string The hashed password
   */
  "default_hash_method" => function ($password) {
    return password_hash($password, PASSWORD_BCRYPT);
  },

  /**
   * This defines the default password verifying method
   * @param string $password
   * @param string $hashed_password
   * @return bool
   */
  "default_password_verify" => function ($password, $hashed_password) {
    return password_verify($password, $hashed_password);
  },

  /**
   * This defines the relation between the Model and the id saved,
   * This closure is binded with the Auth object
   * @param $id The id saved
   */
  "default_id_relation" => function ($id) {
    return $this->model::find($id);
  },

  /**
   * This var is the key's name in session
   */
  "session_key_name" => "___auth___",
];
