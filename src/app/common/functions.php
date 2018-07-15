<?php

/**
 * Objects
 */
function get_or_else( $array, $index, $else ){
  if(array_key_exists($index, $array)) return $array[$index];
  return $else;
}

function inspect($var){
  println("<pre>");
  var_dump($var);
  println("</pre>");
}


/**
 * Integers
 */
function is_between( $value, $min, $max ){
  return ($value > $min && $value < $max);
}


/**
 * Time/date
 */
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


/**
 * Templates
 */
function render($template, $vars = []){
  \app\template::render($template, $vars);
}

function render_to_string($template, $vars = []){
  return \app\template::render_to_string($template, $vars);
}


/**
 * Queries
 */
function sql_find($query, $params = []){
  return \app\database::find($query, $params);
}

function sql_set($query, $params = []){
  return \app\database::set($query, $params);
}


/**
 * Other common files
 */
require_once APP_ROOT."common/files.php";
require_once APP_ROOT."common/images.php";
require_once APP_ROOT."common/text.php";
?>
