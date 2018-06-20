<?php
function api_fail(){}
function web_fail(){
  //header('Location: '.SITE_ROOT.'/403.php');
}

if(!\user::current()->is_logged_in){
  http_response_code(403);

  if(defined("API_ROOT")) api_fail();
  else web_fail();

  die("Not authorized.");
}
?>
