<?php
require_once __DIR__ . '/../app/auth.php';require_once __DIR__ . '/../app/util.php';
$user=require_login();require_perm($user,'incidents.edit');
$in=json_input();$id=intval($in['id']??0);$reason=trim((string)($in['edit_reason']??''));
if($id<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing id']);exit;}
if($reason===''){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing edit_reason']);exit;}
$beforeStmt=db()->prepare("SELECT * FROM incidents WHERE id=?");$beforeStmt->execute([$id]);$before=$beforeStmt->fetch();
if(!$before){http_response_code(404);header('Content-Type: application/json');echo json_encode(['error'=>'Not found']);exit;}
$after=$before;
$after['incident_date']=$in['incident_date']??$before['incident_date'];
$after['incident_time']=$in['incident_time']??$before['incident_time'];
$after['location']=$in['location']??$before['location'];
$after['cctv_available']=array_key_exists('cctv_available',$in)?$in['cctv_available']:$before['cctv_available'];
$after['approved_manager_name']=$in['approved_manager_name']??$before['approved_manager_name'];
$after['reporting_person_name']=$in['reporting_person_name']??$before['reporting_person_name'];
$after['incident_type']=$in['incident_type']??$before['incident_type'];
$auths=(isset($in['authorities_notified'])&&is_array($in['authorities_notified']))?$in['authorities_notified']:null;
$after['authorities_notified_json']=$auths!==null?json_encode($auths):$before['authorities_notified_json'];
$after['police_notified']=normalize_bool($in['police_notified']??$before['police_notified']);
$after['physical_force']=normalize_bool($in['physical_force']??$before['physical_force']);
$after['incident_details']=$in['incident_details']??$before['incident_details'];
$after['actions_taken']=$in['actions_taken']??$before['actions_taken'];
$upd=db()->prepare("UPDATE incidents SET incident_date=?,incident_time=?,location=?,cctv_available=?,approved_manager_name=?,reporting_person_name=?,incident_type=?,authorities_notified_json=?,police_notified=?,physical_force=?,incident_details=?,actions_taken=? WHERE id=?");
$upd->execute([$after['incident_date'],$after['incident_time'],$after['location'],$after['cctv_available'],$after['approved_manager_name'],$after['reporting_person_name'],$after['incident_type'],$after['authorities_notified_json'],$after['police_notified'],$after['physical_force'],$after['incident_details'],$after['actions_taken'],$id]);
$diff=diff_assoc($before,$after);
db()->prepare("INSERT INTO incident_revisions (incident_id,edited_by_staff_id,edit_reason,before_json,after_json,diff_json) VALUES (?,?,?,?,?,?)")
  ->execute([$id,intval($user['id']),$reason,json_encode($before),json_encode($after),json_encode($diff)]);
audit_log(intval($user['id']),'UPDATE','incident',$id,$before,$after,['edit_reason'=>$reason]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);
