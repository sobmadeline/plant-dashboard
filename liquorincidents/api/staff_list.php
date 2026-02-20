<?php
require_once __DIR__ . '/../app/auth.php';
$user=require_login();
if(!has_perm($user,'incidents.create') && !has_perm($user,'refusals.create') && !has_perm($user,'admin.permissions.manage')){
  http_response_code(403);header('Content-Type: application/json');echo json_encode(['error'=>'Forbidden']);exit;
}
$q=trim($_GET['q']??'');
if($q!==''){ $stmt=db()->prepare("SELECT id,name,role,active FROM staff_users WHERE name LIKE ? ORDER BY name LIMIT 100"); $stmt->execute(['%'.$q.'%']); }
else { $stmt=db()->prepare("SELECT id,name,role,active FROM staff_users ORDER BY name LIMIT 200"); $stmt->execute(); }
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
