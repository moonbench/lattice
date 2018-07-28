<?php
require_once __DIR__ . '/main.php';

printlog('Populating database tables:');
\app\database::sql('SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";');
\app\database::beginTransaction('populate_db');

printlog('Populating user table...');
\app\database::sql("
INSERT INTO `${prefix}users` (`id`, `name`, `email`, `password_hash`, `created_at`, `deleted_at`)
VALUES
(UUID_SHORT(), 'Someone', 'Someone@site.com', '', NOW(), NULL);
");
printlog('Done.');

\app\database::commit('populate_db');
printlog('Done with all tables!');
?>


