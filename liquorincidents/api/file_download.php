<?php
require_once __DIR__ . '/../app/auth.php';require_once __DIR__ . '/../app/config.php';
$user=require_login();
$entity=(string)($_GET['entity']??'');$fileId=intval($_GET['id']??0);
if(!$entity||$fileId<=0){http_response_code(400);echo "Bad request";exit;}
$cfg=liq_config();$dir=$cfg['upload_dir'];
if($entity==='incident'){ require_perm($user,'incidents.view'); $stmt=db()->prepare("SELECT stored_name, original_name, mime_type FROM incident_files WHERE id=?"); }
else if($entity==='refusal'){ require_perm($user,'refusals.view'); $stmt=db()->prepare("SELECT stored_name, original_name, mime_type FROM refusal_files WHERE id=?"); }
else { http_response_code(400); echo "Bad entity"; exit; }
$stmt->execute([$fileId]);$row=$stmt->fetch();
if(!$row){http_response_code(404);echo "Not found";exit;}
$path=rtrim($dir,'/\\').DIRECTORY_SEPARATOR.$row['stored_name'];
if(!file_exists($path)){http_response_code(404);echo "Missing file";exit;}
header('Content-Type: '.$row['mime_type']);
header('Content-Disposition: inline; filename="'.preg_replace('/[^a-zA-Z0-9\.\-\_ ]/','_',$row['original_name']).'"');
readfile($path);
