<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg,$code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function ok($extra=[]){ echo json_encode(array_merge(['ok'=>true], $extra)); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('POST required', 405);

// Accept JSON or form-encoded
$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
  $json = json_decode($raw, true);
  if (is_array($json)) $data = $json;
}
if (!$data) $data = $_POST;

$id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : null;
$title = trim((string)($data['title'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$category = trim((string)($data['category'] ?? 'Notice'));
$audience = trim((string)($data['audience'] ?? 'All'));
$email_to = trim((string)($data['email_to'] ?? ''));

$start_at = isset($data['start_at']) ? trim((string)$data['start_at']) : '';
$end_at = isset($data['end_at']) ? trim((string)$data['end_at']) : '';
$start_at = ($start_at === '' ? null : $start_at);
$end_at = ($end_at === '' ? null : $end_at);

if ($title === '' && $message === '') fail('Title or message required');

try{
  if ($id){
    $sql = "UPDATE noticeboard
            SET title=:title, message=:message, category=:category, audience=:audience,
                start_at=:start_at, end_at=:end_at, email_to=:email_to
            WHERE id=:id LIMIT 1";
    $st = db()->prepare($sql);
    $st->execute([
      ':title'=>$title, ':message'=>$message, ':category'=>$category, ':audience'=>$audience,
      ':start_at'=>$start_at, ':end_at'=>$end_at, ':email_to'=>$email_to, ':id'=>$id
    ]);
    ok(['id'=>$id, 'updated'=>$st->rowCount()]);
  } else {
    $sql = "INSERT INTO noticeboard (title, message, category, audience, start_at, end_at, email_to, created_at)
            VALUES (:title,:message,:category,:audience,:start_at,:end_at,:email_to,UTC_TIMESTAMP())";
    $st = db()->prepare($sql);
    $st->execute([
      ':title'=>$title, ':message'=>$message, ':category'=>$category, ':audience'=>$audience,
      ':start_at'=>$start_at, ':end_at'=>$end_at, ':email_to'=>$email_to
    ]);
    ok(['id'=>(int)db()->lastInsertId(), 'created'=>true]);
  }
} catch(Throwable $e){
  fail($e->getMessage(), 500);
}
