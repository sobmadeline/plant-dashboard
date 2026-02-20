<?php
require_once __DIR__ . '/../app/auth.php';require_once __DIR__ . '/../app/util.php';
$user=require_login();require_perm($user,'refusals.edit');
$in=json_input();$id=intval($in['id']??0);$reasonTxt=trim((string)($in['edit_reason']??''));
if($id<=0){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing id']);exit;}
if($reasonTxt===''){http_response_code(400);header('Content-Type: application/json');echo json_encode(['error'=>'Missing edit_reason']);exit;}
$beforeStmt=db()->prepare("SELECT * FROM refusals WHERE id=?");$beforeStmt->execute([$id]);$before=$beforeStmt->fetch();
if(!$before){http_response_code(404);header('Content-Type: application/json');echo json_encode(['error'=>'Not found']);exit;}
$after=$before;
$after['refusal_date']=$in['refusal_date']??$before['refusal_date'];
$after['refusal_time']=$in['refusal_time']??$before['refusal_time'];
$after['location']=$in['location']??$before['location'];
$after['cctv_available']=array_key_exists('cctv_available',$in)?$in['cctv_available']:$before['cctv_available'];
$after['reason']=$in['reason']??$before['reason'];
$after['comments']=$in['comments']??$before['comments'];
$after['approved_manager_name']=$in['approved_manager_name']??$before['approved_manager_name'];
$after['police_notified']=normalize_bool($in['police_notified']??$before['police_notified']);
$upd=db()->prepare("UPDATE refusals SET refusal_date=?,refusal_time=?,location=?,cctv_available=?,reason=?,comments=?,approved_manager_name=?,police_notified=? WHERE id=?");
$upd->execute([$after['refusal_date'],$after['refusal_time'],$after['location'],$after['cctv_available'],$after['reason'],$after['comments'],$after['approved_manager_name'],$after['police_notified'],$id]);
$diff=diff_assoc($before,$after);
db()->prepare("INSERT INTO refusal_revisions (refusal_id,edited_by_staff_id,edit_reason,before_json,after_json,diff_json) VALUES (?,?,?,?,?,?)")
  ->execute([$id,intval($user['id']),$reasonTxt,json_encode($before),json_encode($after),json_encode($diff)]);
audit_log(intval($user['id']),'UPDATE','refusal',$id,$before,$after,['edit_reason'=>$reasonTxt]);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true]);
