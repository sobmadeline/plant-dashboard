<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function ok($extra=[]){ echo json_encode(array_merge(['ok'=>true], $extra)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('POST required', 405);

try{
  $pdo = db();
  $pdo->beginTransaction();

  $sql = "SELECT id FROM handover
          WHERE active=1 AND (acknowledged=0 OR acknowledged IS NULL)
          ORDER BY created_at DESC, id DESC
          LIMIT 1 FOR UPDATE";
  $st = $pdo->query($sql);
  $row = $st->fetch();

  if (!$row){
    $pdo->commit();
    ok(['updated'=>0]);
  }

  $id = (int)$row['id'];
  $st2 = $pdo->prepare("UPDATE handover
                        SET acknowledged=1, acknowledged_at=UTC_TIMESTAMP(), active=0
                        WHERE id=:id LIMIT 1");
  $st2->execute([':id'=>$id]);
  $pdo->commit();

  ok(['updated'=>$st2->rowCount(), 'id'=>$id]);
} catch(Throwable $e){
  try{ db()->rollBack(); } catch(Throwable $_) {}
  fail($e->getMessage(), 500);
}
