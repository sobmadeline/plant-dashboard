<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
$user=require_login();
$in=json_input();
$entityType=(string)($in['entity_type']??'');
$entityId=intval($in['entity_id']??0);
$ttlMin=intval($in['ttl_min']??15);
if(!$entityType||$entityId<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing entity_type/entity_id']);exit;}
$expires=new DateTime();$expires->modify("+{$ttlMin} minutes");
$stmt=db()->prepare("SELECT locked_by_staff_id, locked_at, expires_at FROM record_locks WHERE entity_type=? AND entity_id=?");
$stmt->execute([$entityType,$entityId]);$cur=$stmt->fetch();
if($cur){
  $exp=$cur['expires_at']?strtotime($cur['expires_at']):0;
  if($exp && $exp<time()) $cur=null;
  else if(intval($cur['locked_by_staff_id'])!==intval($user['id'])){
    header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'warning'=>true,'lock'=>$cur]);exit;
  }
}
db()->prepare("INSERT INTO record_locks (entity_type, entity_id, locked_by_staff_id, expires_at)
               VALUES (?,?,?,?)
               ON DUPLICATE KEY UPDATE locked_by_staff_id=VALUES(locked_by_staff_id), expires_at=VALUES(expires_at), locked_at=CURRENT_TIMESTAMP")
  ->execute([$entityType,$entityId,intval($user['id']),$expires->format('Y-m-d H:i:s')]);
audit_log(intval($user['id']),'LOCK_ACQUIRE',$entityType,$entityId,null,null,['expires_at'=>$expires->format(DATE_ATOM)]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'warning'=>false]);
