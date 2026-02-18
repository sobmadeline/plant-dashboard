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

$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id <= 0) fail('id required');

try{
  $st = db()->prepare("DELETE FROM noticeboard WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  ok(['deleted'=>$st->rowCount()]);
} catch(Throwable $e){
  fail($e->getMessage(), 500);
}
