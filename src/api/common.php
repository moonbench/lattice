<?php

function fail( $issues ){
  if( !is_array($issues) ) $issues = [$issues];
  return ["succeeded" => false, "problems" => $issues];
}
function success($object = null){
  return ["succeeded" => true, "output" => $object];
}

?>
