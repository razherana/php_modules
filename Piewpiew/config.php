<?php

// Ensure user or the framework developer configs this part
// throw new Exception("Please configure Piewpiew before using it.");

/**
 * Contains all config for views
 */
return [

  /**
   * Contains the folder of views
   */
  "folder" => "../views",

  /**
   * Contains the folder for compiled views
   */
  "compiled" => "../storage/views",

  /**
   * Contains the map file for the compiled views
   */
  "map" => "../storage/map",

  /**
   * Tells if we always compile in every request
   */
  "always_compile" => true
];
