<?php
require_once __DIR__ . '/main.php';

printlog('Creating database tables:');
\app\database::beginTransaction('create_tables');

printlog("Making users table...");
\app\database::sql("
CREATE TABLE IF NOT EXISTS `${prefix}users` (
  id BIGINT(20) NOT NULL,
  name VARCHAR(64) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(128) NOT NULL,
  created_at DATETIME NOT NULL,
  deleted_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
printlog("Done.");

printlog("Making images table...");
\app\database::sql("
CREATE TABLE IF NOT EXISTS `${prefix}images` (
  id BIGINT(20) NOT NULL,
  owner BIGINT(20) DEFAULT NULL,
  filename VARCHAR(127) NOT NULL,
  original_filename VARCHAR(255) DEFAULT NULL,
  url VARCHAR(255) NOT NULL,
  thumb_small_url VARCHAR(255) DEFAULT NULL,
  thumb_medium_url VARCHAR(255) DEFAULT NULL,
  width INT(10) DEFAULT NULL,
  height INT(10) DEFAULT NULL,
  size INT(15) DEFAULT NULL,
  hash VARCHAR(64) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  deleted_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (url),
  KEY (thumb_small_url),
  KEY (thumb_medium_url),
  KEY (owner)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
printlog("Done.");

printlog("Making sessions table...");
\app\database::sql("
CREATE TABLE IF NOT EXISTS `${prefix}sessions` (
  id BIGINT(20) NOT NULL,
  user BIGINT(20) NOT NULL,
  token VARCHAR(255) NOT NULL,
  ip VARCHAR(127) NOT NULL,
  user_agent VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  expires_at DATETIME DEFAULT NULL,
  deleted_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (user),
  KEY (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
printlog("Done.");

printlog("Making login_attempts table...");
\app\database::sql("
CREATE TABLE IF NOT EXISTS `${prefix}login_attempts` (
  id INT(10) NOT NULL AUTO_INCREMENT,
  user BIGINT(20) DEFAULT NULL,
  successful BOOLEAN NOT NULL DEFAULT 0,
  ip VARCHAR(127) NOT NULL,
  user_agent VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  deleted_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY (user),
  KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
printlog("Done.");

\app\database::commit('create_tables');
printlog('Done with all tables!');
?>
