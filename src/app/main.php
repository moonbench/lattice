<?php
namespace app;
define('APP_ROOT', dirname(__FILE__).'/');

set_time_limit(10);
if(session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once APP_ROOT.'syntactic_sugar.php';
require_once APP_ROOT.'autoloader.php';
new \app\autoloader();
set_error_handler('\app\error::php_error');

\app\config::load_site_config();

ob_start('ob_gzhandler');
?>
