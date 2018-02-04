<?php
/*
 * Simple authentication gate
 *
 * This an be included on any script
 * It will require that the user be authenticated with a valid session
 * Otherwise they will recieve a 403 error and execution will terminate
 */

if( !\user::current()->is_logged_in ){
  http_response_code(403);
  header('Location: /login?desired_request=' . $_SERVER["REQUEST_URI"]);
  die("Not authorized.");
}

?>
