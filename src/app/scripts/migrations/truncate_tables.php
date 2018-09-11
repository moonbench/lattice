<?php
require_once __DIR__ . '/main.php';

printlog('Truncating database tables:');
\app\database::beginTransaction('empty_db');

printlog('Truncating users table...');
\app\database::sql("TRUNCATE TABLE `${prefix}users`;");
printlog('Done.');

printlog('Truncating sessions table...');
\app\database::sql("TRUNCATE TABLE `${prefix}sessions`;");
printlog('Done.');

printlog('Truncating images table...');
\app\database::sql("TRUNCATE TABLE `${prefix}images`;");
printlog('Done.');

printlog('Truncating login_attempt table...');
\app\database::sql("TRUNCATE TABLE `${prefix}login_attempts`;");
printlog('Done.');

\app\database::commit('empty_db');
printlog('Done with all tables!');
?>
