<?php
namespace app;
/**
 * Main entry point into the framework
 *
 * Once included in any script, this will set up a common environment
 * which provides access to the framework
 */

/**
 * Identify where we are running from and configure the root values accordingly
 */
function determine_paths(){
  // Where is the framework's root?
  define('APP_ROOT', dirname(__FILE__));
  // Get the site root from the default config
  $site_root = parse_ini_file(APP_ROOT . "/config/site.default.ini")["site_root"];
  // overwrite with local config if it exists
  $local_config = APP_ROOT . "/config/site.ini";
  if(file_exists($local_config)){
    $site_root = parse_ini_file($local_config)["site_root"];
  }
  define('SITE_ROOT', $site_root);  
}
/**
 * Make PHP more comfortable with custom functions
 */
function add_common_functions(){
  require_once APP_ROOT . "/common/fn.php";
}
/**
 * Bind functions into the PHP environment to automatically handle things
 */
function add_support_classes(){
  require_once APP_ROOT . '/autoloader.php';
  new \app\autoloader();
  set_error_handler('\app\error::php_error');
}
/**
 * Make PHP run within known constraints
 */
function adjust_the_environment(){
  set_time_limit(45);
}
/**
 * Make sure that we have a user session running prior to code execution
 */
function prepare_user_session(){
  if( session_status() !== PHP_SESSION_ACTIVE ) session_start();
}
/**
 * Start an output buffer to prevent writing to the client by mistake
 */
function start_output_buffer(){
  ob_start("ob_gzhandler");
}
/**
 * Start the framework
 */
determine_paths();
add_common_functions();
add_support_classes();
adjust_the_environment();
prepare_user_session();
start_output_buffer();
// Ready for work!
?>
