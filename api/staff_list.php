<?php
$GLOBALS['REQUIRE_ADMIN'] = true;
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';

try {
  $rows = db()->query("SELECT id, name, role, rfid_uid, active, created_at FROM staff_users ORDER BY active DESC, role DESC, name ASC")->fetchAll();
  echo json_encode(['ok'=>true,'rows'=>$rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
