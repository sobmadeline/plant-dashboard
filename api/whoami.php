<?php
require_once __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['staff']) || !is_array($_SESSION['staff']) || !isset($_SESSION['staff']['id'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Unauthorised']);
  exit;
}

echo json_encode([
  'ok'=>true,
  'user'=>[
    'id' => intval($_SESSION['staff']['id']),
    'display_name' => (string)($_SESSION['staff']['name'] ?? 'Unknown'),
    'role' => (string)($_SESSION['staff']['role'] ?? 'staff'),
  ]
]);
