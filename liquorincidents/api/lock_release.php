<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
$user=require_login();
$in=json_input();
$entityType=(string)($in['entity_type']??'');
$entityId=intval($in['entity_id']??0);
if(!$entityType||$entityId<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing entity_type/entity_id']);exit;}
db()->prepare("DELETE FROM record_locks WHERE entity_type=? AND entity_id=? AND locked_by_staff_id=?")
  ->execute([$entityType,$entityId,intval($user['id'])]);
audit_log(intval($user['id']),'LOCK_RELEASE',$entityType,$entityId);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);
