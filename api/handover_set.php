<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function ok($extra=[]){ echo json_encode(array_merge(['ok'=>true], $extra)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('POST required', 405);

$raw = file_get_contents('php://input');
$data = [];
if ($raw) { $json = json_decode($raw, true); if (is_array($json)) $data = $json; }
if (!$data) $data = $_POST;

$msg = trim((string)($data['message'] ?? ''));
$email_to = trim((string)($data['email_to'] ?? ''));

if ($msg === '') fail('message required');

try{
  $pdo = db();
  $pdo->beginTransaction();

  // deactivate any previous active handovers
  $pdo->exec("UPDATE handover SET active=0 WHERE active=1");

  $st = $pdo->prepare("INSERT INTO handover (active, message, created_at, acknowledged, acknowledged_at, email_to)
                       VALUES (1, :msg, UTC_TIMESTAMP(), 0, NULL, :email_to)");
  $st->execute([':msg'=>$msg, ':email_to'=>$email_to]);

  $id = (int)$pdo->lastInsertId();
  $pdo->commit();

  ok(['id'=>$id]);
} catch(Throwable $e){
  try{ db()->rollBack(); } catch(Throwable $_) {}
  fail($e->getMessage(), 500);
}
