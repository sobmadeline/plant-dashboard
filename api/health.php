<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $count = db()->query("SELECT COUNT(*) AS c FROM plant_status")->fetch()['c'] ?? 0;
  echo json_encode([
    'ok' => true,
    'plant_status_count' => (int)$count,
    'time' => date('c'),
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => $e->getMessage(),
  ]);
}
