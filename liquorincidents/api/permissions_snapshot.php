<?php
require_once __DIR__ . '/../app/auth.php';
$user=require_login();require_perm($user,'admin.permissions.manage');
$perms=db()->query("SELECT perm_key,label FROM permissions ORDER BY perm_key")->fetchAll();
$roles=db()->query("SELECT role_key,label FROM roles ORDER BY role_key")->fetchAll();
$rolePerms=db()->query("SELECT role_key,perm_key,allowed FROM role_permissions")->fetchAll();
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'permissions'=>$perms,'roles'=>$roles,'role_permissions'=>$rolePerms]);
