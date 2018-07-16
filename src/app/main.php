<?php
namespace app;

define('APP_ROOT', dirname(__FILE__).'/');

function add_common_functions(){
  require_once APP_ROOT.'common/functions.php';
}

function add_support_classes(){
  require_once APP_ROOT.'autoloader.php';
  new \app\autoloader();
  set_error_handler('\app\error::php_error');
}

set_time_limit(45);

add_common_functions();
add_support_classes();

\app\config::load_site_config();

if(session_status() !== PHP_SESSION_ACTIVE) session_start();
ob_start('ob_gzhandler');
?>
