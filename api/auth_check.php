<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$require_admin = false;
if (isset($GLOBALS['REQUIRE_ADMIN']) && $GLOBALS['REQUIRE_ADMIN'] === true) {
  $require_admin = true;
}

if (!isset($_SESSION['staff'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
}

if ($require_admin && (($_SESSION['staff']['role'] ?? '') !== 'admin')) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Admin required']);
  exit;
}

echo json_encode(['ok'=>true,'user'=>$_SESSION['staff']]);
