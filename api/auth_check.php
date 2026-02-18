<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['staff'])) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
}

echo json_encode(['ok'=>true, 'user'=>$_SESSION['staff']]);
