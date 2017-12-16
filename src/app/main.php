<?php
namespace app;

/*
 * Bootstrap the application
 */

// Use the location of this file as a relative root for the framework
define('APP_ROOT', dirname(__FILE__));


// Get the site root from the default config
$site_root = parse_ini_file(APP_ROOT . "/config/site.default.ini")["site_root"];

// overwrite with local config if it exists
$local_config = APP_ROOT . "/config/site.ini";
if(file_exists($local_config)){
  $site_root = parse_ini_file($local_config)["site_root"];
}
define('SITE_ROOT', $site_root);


// Make PHP more friendly
require_once APP_ROOT . "/common/fn.php";

// Prepare the support classes
require_once APP_ROOT . '/autoloader.php';
new \app\autoloader();
set_error_handler('\app\error::php_error');


// Adjustment the environment
set_time_limit(45);


// Prepare session
if( session_status() !== PHP_SESSION_ACTIVE ) session_start();


// Create an output buffer to control the
ob_start("ob_gzhandler");
?>