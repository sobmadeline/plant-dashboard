<?php
$GLOBALS['REQUIRE_ADMIN'] = true;
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$in = json_decode(file_get_contents('php://input'), true) ?? [];
$name = trim($in['name'] ?? '');
$pin = trim($in['pin'] ?? '');
$rfid = isset($in['rfid_uid']) ? trim((string)$in['rfid_uid']) : '';
$role = trim($in['role'] ?? 'staff');

if ($name === '' || $pin === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Name and PIN are required']);
  exit;
}
if (!preg_match('/^\d{4,8}$/', $pin)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'PIN must be 4–8 digits']);
  exit;
}
if ($role !== 'staff' && $role !== 'admin') $role = 'staff';

try {
  $hash = password_hash($pin, PASSWORD_DEFAULT);
  $stmt = db()->prepare("INSERT INTO staff_users (name, pin_hash, rfid_uid, role, active) VALUES (?,?,?,?,1)");
  $stmt->execute([$name, $hash, ($rfid !== '' ? $rfid : null), $role]);
  echo json_encode(['ok'=>true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
