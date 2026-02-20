<?php
require_once __DIR__ . '/../app/auth.php';
$user=require_login();require_perm($user,'admin.permissions.manage');
$staffId=intval($_GET['staff_id']??0);
if($staffId<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing staff_id']);exit;}
$rows=db()->prepare("SELECT perm_key, effect, reason, updated_at, updated_by_staff_id FROM user_permissions WHERE staff_id=? ORDER BY perm_key");
$rows->execute([$staffId]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'items'=>$rows->fetchAll()]);
