<?php
/**
 * Common functions available to all API endpoints
 *
 * Provides standard response formats
 */

/**
 * Respond with a negative success value with an array of errors
 */
function fail( $issues ){
  if( !is_array($issues) ) $issues = [$issues];
  return ["succeeded" => false, "problems" => $issues];
}

/**
 * Respond with a positive success value with the manipulated object (if any)
 */
function success($object = null){
  return ["succeeded" => true, "output" => $object];
}

?>
