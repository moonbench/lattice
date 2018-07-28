<?php
require_once __DIR__ . '/main.php';

printlog('Dropping database tables:');
\app\database::beginTransaction('drop_tables');

printlog('Dropping users table...');
\app\database::sql("DROP TABLE `${prefix}users`;");
printlog('Done.');

printlog('Dropping sessions table...');
\app\database::sql("DROP TABLE `${prefix}sessions`;");
printlog('Done.');

printlog('Dropping login_attempts table...');
\app\database::sql("DROP TABLE `${prefix}login_attempts`;");
printlog('Done.');

printlog('Dropping images table...');
\app\database::sql("DROP TABLE `${prefix}images`;");
printlog('Done.');

\app\database::commit('drop_tables');
printlog('Done with all tables!');
?>
