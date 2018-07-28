<?php
require_once __DIR__ . '/../../main.php';
define('MIGRATIONS_ENABLED', false);

function parameter_check(){
  $opts = getopt("",["cli:"]);
  if(!(array_key_exists("cli", $opts) && $opts["cli"]=="1")){
    println("Error: --cli required");
    return false;
  }
  return true;
}

if(MIGRATIONS_ENABLED && parameter_check()){
  $prefix = \app\config::db('prefix');
  \app\database::sql('
    SET AUTOCOMMIT = 0;
    SET time_zone = "+00:00";
  ');
} else {
  println('Error: Scripts disabled');
  die();
}

?>
