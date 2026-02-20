<?php
require_once __DIR__ . '/../app/auth.php';require_once __DIR__ . '/../app/util.php';require_once __DIR__ . '/../app/config.php';
$user=require_login();require_perm($user,'incidents.edit');
$incidentId=intval($_POST['incident_id']??0);if($incidentId<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing incident_id']);exit;}
$cfg=liq_config();$dir=$cfg['upload_dir'];if(!is_dir($dir))@mkdir($dir,0775,true);
if(!isset($_FILES['file'])){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing file']);exit;}
$f=$_FILES['file'];if($f['error']!==UPLOAD_ERR_OK){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Upload failed']);exit;}
if($f['size']>$cfg['max_upload_bytes']){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'File too large']);exit;}
$mime=mime_content_type($f['tmp_name'])?:($f['type']??'application/octet-stream');
if(!in_array($mime,$cfg['allowed_mimes'],true)){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'File type not allowed']);exit;}
$orig=basename($f['name']);$ext=pathinfo($orig,PATHINFO_EXTENSION);$stored=rand_name($ext);$dest=rtrim($dir,'/\\').DIRECTORY_SEPARATOR.$stored;
if(!move_uploaded_file($f['tmp_name'],$dest)){http_response_code(500);header('Content-Type: application/json');echo json_encode(['error'=>'Could not save file']);exit;}
db()->prepare("INSERT INTO incident_files (incident_id,uploaded_by_staff_id,original_name,stored_name,mime_type,size_bytes) VALUES (?,?,?,?,?,?)")
  ->execute([$incidentId,intval($user['id']),$orig,$stored,$mime,intval($f['size'])]);
$fileId=intval(db()->lastInsertId());
audit_log(intval($user['id']),'UPLOAD_FILE','incident',$incidentId,null,null,['file_id'=>$fileId,'original_name'=>$orig,'mime'=>$mime,'size'=>$f['size']]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'file_id'=>$fileId]);
