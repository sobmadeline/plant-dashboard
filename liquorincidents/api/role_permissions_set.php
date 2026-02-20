<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
$user=require_login();require_perm($user,'admin.permissions.manage');
$in=json_input();
$role=strtolower(trim((string)($in['role_key']??'')));$perm=(string)($in['perm_key']??'');$allowed=normalize_bool($in['allowed']??0);
if(!$role||!$perm){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing role_key or perm_key']);exit;}
$beforeStmt=db()->prepare("SELECT allowed FROM role_permissions WHERE role_key=? AND perm_key=?");$beforeStmt->execute([$role,$perm]);$before=$beforeStmt->fetch();
db()->prepare("INSERT INTO role_permissions (role_key, perm_key, allowed) VALUES (?,?,?) ON DUPLICATE KEY UPDATE allowed=VALUES(allowed)")
  ->execute([$role,$perm,$allowed]);
audit_log(intval($user['id']),'PERM_ROLE_SET','role_permission',null,['role_key'=>$role,'perm_key'=>$perm,'allowed'=>$before?intval($before['allowed']):null],['role_key'=>$role,'perm_key'=>$perm,'allowed'=>$allowed]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);
