<?php
require_once __DIR__ . '/../../main.php';

# Change this to enable migrations
define('MIGRATIONS_ENABLED', false);

function parameter_check(){
  return get_or_else(getopt("",["cli:"]), "cli") == '1';
}

if(!MIGRATIONS_ENABLED){
  println("Error: Migrations are disabled.");
  println("View '" . dirname(__FILE__) . "' for more information.");
  die();
} elseif (!parameter_check()){
  println("Error: Please add --cli=1 to execute command-line script.");
  die();
} else {
  $prefix = \app\config::db('prefix');
  \app\database::sql('
    SET AUTOCOMMIT = 0;
    SET time_zone = "+00:00";
  ');
}

?>
