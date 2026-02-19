<?php
function get_config(): array {
  static $cfg = null;
  if ($cfg !== null) return $cfg;
  $path = __DIR__ . '/config.php';
  if (!file_exists($path)) {
    http_response_code(500);
    echo "Missing incidents/app/config.php.";
    exit;
  }
  $cfg = require $path;
  return $cfg;
}

function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $cfg = get_config()['db'];
  $port = $cfg['port'] ?? 3306;
  $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=%s", $cfg['host'], $port, $cfg['name'], $cfg['charset']);
  $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
