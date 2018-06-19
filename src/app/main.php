<?php
namespace app;

function determine_paths(){
  define('APP_ROOT', dirname(__FILE__));

  $site_root = parse_ini_file(APP_ROOT . "/config/site.default.ini")["site_root"];

  // overwrite with local config if it exists
  $local_config = APP_ROOT . "/config/site.ini";
  if(file_exists($local_config)) $site_root = parse_ini_file($local_config)["site_root"];
  define('SITE_ROOT', $site_root);  
}

function add_common_functions(){
  require_once APP_ROOT . "/common/fn.php";
}

function add_support_classes(){
  require_once APP_ROOT . '/autoloader.php';
  new \app\autoloader();
  set_error_handler('\app\error::php_error');
}

set_time_limit(45);

determine_paths();
add_common_functions();
add_support_classes();

if( session_status() !== PHP_SESSION_ACTIVE ) session_start();
ob_start("ob_gzhandler");
?>
