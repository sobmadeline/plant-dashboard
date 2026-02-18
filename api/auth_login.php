<?php
require_once __DIR__ . '/db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$pin = trim($input['pin'] ?? '');
$rfid = trim($input['rfid'] ?? '');

try {
  if ($rfid !== '') {
    $stmt = db()->prepare("SELECT id, name, role FROM staff_users WHERE active=1 AND rfid_uid=?");
    $stmt->execute([$rfid]);
  } else {
    if ($pin === '') {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'PIN or RFID required']);
      exit;
    }
    $stmt = db()->prepare("SELECT id, name, role, pin_hash FROM staff_users WHERE active=1");
    $stmt->execute();
    $user = null;
    foreach ($stmt as $row) {
      if (password_verify($pin, $row['pin_hash'])) {
        $user = $row;
        break;
      }
    }
    if (!$user) {
      http_response_code(401);
      echo json_encode(['ok'=>false,'error'=>'Invalid PIN']);
      exit;
    }
    $_SESSION['staff'] = ['id'=>$user['id'], 'name'=>$user['name'], 'role'=>$user['role']];
    echo json_encode(['ok'=>true, 'user'=>$_SESSION['staff']]);
    exit;
  }

  $user = $stmt->fetch();
  if (!$user) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Unknown RFID']);
    exit;
  }

  $_SESSION['staff'] = ['id'=>$user['id'], 'name'=>$user['name'], 'role'=>$user['role']];
  echo json_encode(['ok'=>true, 'user'=>$_SESSION['staff']]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
