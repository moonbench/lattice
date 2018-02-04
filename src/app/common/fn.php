<?php
/*
 * Common functions available everywhere to make coding easier
 */


/**
 * Get the value from an array with the given index if it exists, or return a default value
 */
function get_or_else( $array, $index, $else ){
  if(array_key_exists($index, $array)) return $array[$index];
  return $else;
}

/**
 * Determine if a value is between two values
 */
function is_between( $value, $min, $max ){
  return ($value > $min && $value < $max);
}


/**
 * Trim whitespace and remove excess multiple pairs of newlines from a string
 */
function clean($string){
  return htmlentities(preg_replace('/([\\r\\n][\\r\\n]){2,}/i',"\n\n",trim($string)));
}

/**
 * Clean a string with clean() and convert new lines to HTML breaks
 */
function clean_br($string){
  return nl2br(clean($string));
}

/**
 * Debugging function to format and output the value of a variable
 */
function clean_var_dump($var){
  echo("<pre>");
  var_dump($var);
  echo("</pre>");
  echo("<hr/>");
}

/**
 * Convert a timestamp to a string representing the time since then
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
 * Shordhand function to render a template
 */
function render($template, $vars = []){
  \app\template::render($template, $vars);
}

/**
 * Shorthand function to render a template into a string
 */
function render_to_string($template, $vars = []){
  return \app\template::render_to_string($template, $vars);
}


/**
 * Shorthand function to select from a database
 */
function sql_find($query, $params = []){
  return \app\database::find($query, $params);
}

/**
 * Shorthand function to set in a database
 */
function sql_set($query, $params = []){
  return \app\database::set($query, $params);
}
?>
