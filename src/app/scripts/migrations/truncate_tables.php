<?php
require_once __DIR__ . '/main.php';

printlog('Truncating database tables:');
\app\database::beginTransaction('empty_db');

\app\database::sql('
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
SET time_zone = "+00:00";
');

printlog('Truncating users table...');
\app\database::sql("TRUNCATE TABLE `${prefix}users`;");
printlog('Done.');

printlog('Truncating sessions table...');
\app\database::sql("TRUNCATE TABLE `${prefix}sessions`;");
printlog('Done.');

printlog('Truncating images table...');
\app\database::sql("TRUNCATE TABLE `${prefix}images`;");
printlog('Done.');

\app\database::commit('empty_db');
printlog('Done with all tables!');
?>
