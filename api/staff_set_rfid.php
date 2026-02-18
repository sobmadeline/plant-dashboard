<?php
$GLOBALS['REQUIRE_ADMIN'] = true;
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($in['id'] ?? 0);
$rfid = $in['rfid_uid'] ?? null;
$rfid = is_null($rfid) ? null : trim((string)$rfid);

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'id required']);
  exit;
}

try {
  $stmt = db()->prepare("UPDATE staff_users SET rfid_uid=? WHERE id=?");
  $stmt->execute([$rfid !== '' ? $rfid : null, $id]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
