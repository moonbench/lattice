<?php
namespace app;

define('APP_ROOT', dirname(__FILE__) . "/");

function load_config(){
  $site_config = parse_ini_file(APP_ROOT."config/site.default.ini");
  if(file_exists(APP_ROOT."config/site.ini"))
    $site_config = parse_ini_file(APP_ROOT."config/site.ini");
  define('SITE_CONFIG', $site_config);
  define('SITE_ROOT', SITE_CONFIG["site_root"]);
}

function add_common_functions(){
  require_once APP_ROOT . "common/fn.php";
}

function add_support_classes(){
  require_once APP_ROOT . 'autoloader.php';
  new \app\autoloader();
  set_error_handler('\app\error::php_error');
}

set_time_limit(45);

load_config();
add_common_functions();
add_support_classes();

if(session_status() !== PHP_SESSION_ACTIVE) session_start();
ob_start("ob_gzhandler");
?>
