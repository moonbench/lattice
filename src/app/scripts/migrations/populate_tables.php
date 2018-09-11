<?php
require_once __DIR__ . '/main.php';

printlog('Populating database tables:');
\app\database::beginTransaction('populate_db');

# Add models here

\app\database::commit('populate_db');
printlog('Done with all tables!');
?>


