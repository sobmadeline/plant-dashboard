<?php
$GLOBALS['REQUIRE_ADMIN'] = true;
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($in['id'] ?? 0);
$pin = trim($in['pin'] ?? '');

if ($id <= 0 || $pin === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'id and pin required']);
  exit;
}
if (!preg_match('/^\d{4,8}$/', $pin)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'PIN must be 4–8 digits']);
  exit;
}

try {
  $hash = password_hash($pin, PASSWORD_DEFAULT);
  $stmt = db()->prepare("UPDATE staff_users SET pin_hash=? WHERE id=?");
  $stmt->execute([$hash, $id]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
