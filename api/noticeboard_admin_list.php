<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function ok($rows){ echo json_encode(['ok'=>true,'rows'=>$rows]); exit; }
function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

$scope = isset($_GET['scope']) ? trim((string)$_GET['scope']) : 'active'; // active|all
$where = [];
if ($scope !== 'all') {
  $where[] = "(start_at IS NULL OR start_at <= UTC_TIMESTAMP())";
  $where[] = "(end_at IS NULL OR end_at >= UTC_TIMESTAMP())";
}

$sql = "SELECT id, title, message, category, audience, start_at, end_at, email_to
        FROM noticeboard";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY COALESCE(start_at, created_at) DESC, id DESC LIMIT 200";

try{
  $st = db()->query($sql);
  ok($st->fetchAll());
} catch(Throwable $e){
  fail($e->getMessage(), 500);
}
