<?php
require_once __DIR__ . '/../app/auth.php';
$user=require_login();require_perm($user,'incidents.view');
$id=intval($_GET['id']??0);if($id<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing id']);exit;}
$stmt=db()->prepare("SELECT i.*, s.name AS created_by_name FROM incidents i LEFT JOIN staff_users s ON s.id=i.created_by_staff_id WHERE i.id=?");
$stmt->execute([$id]);$row=$stmt->fetch();if(!$row){http_response_code(404);header('Content-Type: application/json');echo json_encode(['error'=>'Not found']);exit;}
$files=db()->prepare("SELECT id, original_name, mime_type, size_bytes, created_at FROM incident_files WHERE incident_id=? ORDER BY created_at");$files->execute([$id]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'item'=>$row,'files'=>$files->fetchAll()]);
