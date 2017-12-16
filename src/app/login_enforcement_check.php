<?php

/*
 * Can be included in any page to require the user be logged in, otherwise return a 403
 */

if( !\user_controller::current()->is_logged_in ){
  http_response_code(403);
  header('Location: /login?desired_request=' . $_SERVER["REQUEST_URI"]);
  die("Not authorized.");
}

?>
