<?php
/**
 * Debugging
 */
function inspect($variable){
  println("<pre>");
  var_dump($variable);
  println("</pre>");
}

/**
 * String formatting
 */
function println($string){
  echo("${string}\n");
}

function printlog($string){
  println("[".date('r')."] ${string}");
}

function clean($string){
  return htmlentities(preg_replace('/([\\r\\n][\\r\\n]){2,}/i',"\n\n",trim($string)));
}

function clean_br($string){
  return nl2br(clean($string));
}

function make_links_clickable($text){
  return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Z?-??-?()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1" target="_blank">$1</a>', $text);
}

/**
 * Value comparison
 */
function is_between($value, $min, $max){
  return ($value > $min && $value < $max);
}

/**
 * Object & array access
 */
function get_or_else($container, $index, $else=null){
  if(!$container) return $else;

  if(is_object($container))
    if(property_exists($container, $index) && isset($container->$index)) return $container->$index;

  if(is_array($container))
    if(array_key_exists($index, $container) && isset($container[$index])) return $container[$index];

  return $else;
}

/**
 * Time/date formatting
 */
function human_time_ago($time){
  $time_since = time() - $time;

  $units = array (
    31536000 => 'year',
    2592000 => 'month',
    604800 => 'week',
    86400 => 'day',
    3600 => 'hour',
    60 => 'minute',
    1 => 'second'
  );

  foreach ($units as $unit => $label) {
    if ($time_since < $unit) continue;
    $quantity = floor($time_since / $unit);
    return $quantity.' '.$label.(($quantity>1)?'s':'');
  }
}

function sql_date($time = null){
  return date("Y-m-d H:i:s", $time ? $time : time());
}

/**
 * Template rendering
 */
function render($template, $vars = []){
  \app\template::render($template, $vars);
}

function render_to_string($template, $vars = []){
  return \app\template::render_to_string($template, $vars);
}

/**
 * Database qeries
 */
function sql_find($query, $params = []){
  return \app\database::find($query, $params);
}

function sql_set($query, $params = []){
  return \app\database::set($query, $params);
}

/**
 * Simple access gating
 */
function login_enforcement_check(){
  if(!\user::current()->is_logged_in){
    http_response_code(403);
    header('Location: '.SITE_ROOT.'/403.php');
    die("Not authorized.");
  }
  return true;
}
?>
