<?php
$GLOBALS['REQUIRE_ADMIN'] = true;
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($in['id'] ?? 0);
$active = (int)($in['active'] ?? 0);

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'id required']);
  exit;
}

try {
  $stmt = db()->prepare("UPDATE staff_users SET active=? WHERE id=?");
  $stmt->execute([$active ? 1 : 0, $id]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
