<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
$user=require_login();require_perm($user,'admin.permissions.manage');
$in=json_input();
$staffId=intval($in['staff_id']??0);$perm=(string)($in['perm_key']??'');$effect=(string)($in['effect']??'');$reason=(string)($in['reason']??'');
if($staffId<=0||!$perm||!$effect){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing staff_id/perm_key/effect']);exit;}
$beforeStmt=db()->prepare("SELECT effect, reason FROM user_permissions WHERE staff_id=? AND perm_key=?");$beforeStmt->execute([$staffId,$perm]);$before=$beforeStmt->fetch();
if($effect==='delete'){
  db()->prepare("DELETE FROM user_permissions WHERE staff_id=? AND perm_key=?")->execute([$staffId,$perm]);
  audit_log(intval($user['id']),'PERM_USER_DELETE','user_permission',null,['staff_id'=>$staffId,'perm_key'=>$perm,'effect'=>$before['effect']??null,'reason'=>$before['reason']??null],['staff_id'=>$staffId,'perm_key'=>$perm,'effect'=>null,'reason'=>null],['reason'=>$reason]);
  header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);exit;
}
if(!in_array($effect,['allow','deny'],true)){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Invalid effect']);exit;}
db()->prepare("INSERT INTO user_permissions (staff_id, perm_key, effect, reason, updated_by_staff_id)
               VALUES (?,?,?,?,?)
               ON DUPLICATE KEY UPDATE effect=VALUES(effect), reason=VALUES(reason), updated_by_staff_id=VALUES(updated_by_staff_id)")
  ->execute([$staffId,$perm,$effect,$reason,intval($user['id'])]);
audit_log(intval($user['id']),'PERM_USER_SET','user_permission',null,['staff_id'=>$staffId,'perm_key'=>$perm,'effect'=>$before['effect']??null,'reason'=>$before['reason']??null],['staff_id'=>$staffId,'perm_key'=>$perm,'effect'=>$effect,'reason'=>$reason]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);
