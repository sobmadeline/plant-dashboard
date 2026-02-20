<?php
require_once __DIR__ . '/../app/auth.php';require_once __DIR__ . '/../app/util.php';require_once __DIR__ . '/_seq.php';
$user=require_login();require_perm($user,'refusals.create');
$in=json_input();foreach(['refusal_date','refusal_time','reason'] as $r){ if(empty($in[$r])){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>"Missing $r"]);exit;} }
$dt=new DateTime($in['refusal_date'].' 00:00:00');$no=next_register_no($dt);
$stmt=db()->prepare("INSERT INTO refusals (refusal_no,refusal_date,refusal_time,location,cctv_available,reason,comments,approved_manager_name,police_notified,soft_locked,created_by_staff_id) VALUES (?,?,?,?,?,?,?,?,?,1,?)");
$stmt->execute([$no,$in['refusal_date'],$in['refusal_time'],$in['location']??null,array_key_exists('cctv_available',$in)?normalize_bool($in['cctv_available']):null,$in['reason'],$in['comments']??null,$in['approved_manager_name']??($user['name']??null),normalize_bool($in['police_notified']??0),intval($user['id'])]);
$id=intval(db()->lastInsertId());audit_log(intval($user['id']),'CREATE','refusal',$id,null,['refusal_no'=>$no],['refusal_no'=>$no]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'id'=>$id,'refusal_no'=>$no]);
