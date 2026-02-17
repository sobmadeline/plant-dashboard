<?php
// api/config.php (safe to commit without passwords)
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'bractools');

if (file_exists(__DIR__ . '/config.local.php')) {
  require __DIR__ . '/config.local.php';
} else {
  // fallback (optional) — but ideally this file always exists on server
  define('DB_USER', '');
  define('DB_PASS', '');
}
