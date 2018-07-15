<?php
$opts = getopt("",["cli:"]);
if(!(array_key_exists("cli", $opts) && $opts["cli"]=="1")) die("cli only\n");

require_once __DIR__ . '/../main.php';
?>
