<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

try{
  $sql = "SELECT id, active, message, created_at, acknowledged, acknowledged_at
          FROM handover
          WHERE active = 1 AND (acknowledged = 0 OR acknowledged IS NULL)
          ORDER BY created_at DESC, id DESC
          LIMIT 1";
  $st = db()->query($sql);
  $row = $st->fetch();

  if (!$row){
    echo json_encode(['ok'=>true,'active'=>false,'message'=>'','created_at'=>null]);
    exit;
  }

  echo json_encode([
    'ok'=>true,
    'active'=>true,
    'id'=>(int)$row['id'],
    'message'=>$row['message'],
    'created_at'=>$row['created_at'],
  ]);
} catch(Throwable $e){
  fail($e->getMessage(), 500);
}
