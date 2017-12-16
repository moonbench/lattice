<?php

function get_or_else( $array, $index, $else ){
  if(array_key_exists($index, $array)) return $array[$index];
  return $else;
}
function is_between( $value, $min, $max ){
  return ($value > $min && $value < $max);
}
function clean_var_dump($var){
  echo("<pre>");
  var_dump($var);
  echo("</pre>");
  echo("<hr/>");
}

function clean($string){
  return htmlentities(preg_replace('/([\\r\\n][\\r\\n]){2,}/i',"\n\n",trim($string)));
}
function clean_br($string){
  return nl2br(clean($string));
}

function render($template, $vars = []){
  \app\template::render($template, $vars);
}
function render_to_string($template, $vars = []){
  return \app\template::render_to_string($template, $vars);
}

function sql_find($query, $params = []){
  return \database_controller::find($query, $params);
}
function sql_set($query, $params = []){
  return \database_controller::set($query, $params);
}

function human_time_ago($time){
  $time_since = time() - $time;

  $tokens = array (
		   31536000 => 'year',
		   2592000 => 'month',
		   604800 => 'week',
		   86400 => 'day',
		   3600 => 'hour',
		   60 => 'minute',
		   1 => 'second'
		   );

  foreach ($tokens as $unit => $text) {
    if ($time_since < $unit) continue;
    $numberOfUnits = floor($time_since / $unit);
    return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
  }
}
?>