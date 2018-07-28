<?php
require_once __DIR__ . '/../main.php';

function scripts_enabled(){
  return false;
}

function parameter_check(){
  $opts = getopt("",["cli:"]);
  if(!(array_key_exists("cli", $opts) && $opts["cli"]=="1")){
    println("cli required");
    return false;
  }
  return true;
}

if(scripts_enabled() && parameter_check()){
  $prefix = \app\config::db('prefix');
  \app\database::sql('
    SET AUTOCOMMIT = 0;
    SET time_zone = "+00:00";
  ');
} else {
  die("Can not run\n");
}

?>
