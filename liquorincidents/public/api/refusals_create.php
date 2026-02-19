<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

$user = require_login();
$in = json_input();
$required = ['refusal_date','refusal_time','reason'];
foreach ($required as $r) {
  if (empty($in[$r])) { http_response_code(400); echo json_encode(['error'=>"Missing $r"]); exit; }
}

$stmt = db()->prepare("INSERT INTO refusals
  (refusal_no, refusal_date, refusal_time, location, cctv_available, reason, comments, approved_manager_name, police_notified, created_by_user_id)
  VALUES ('', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
  $in['refusal_date'],
  $in['refusal_time'],
  $in['location'] ?? null,
  isset($in['cctv_available']) ? normalize_bool($in['cctv_available']) : null,
  $in['reason'],
  $in['comments'] ?? null,
  $in['approved_manager_name'] ?? ($user['display_name'] ?? null),
  normalize_bool($in['police_notified'] ?? 0),
  intval($user['id']),
]);

$id = intval(db()->lastInsertId());
$no = make_incident_no('REF', str_replace('-','', $in['refusal_date']), $id);
db()->prepare("UPDATE refusals SET refusal_no=? WHERE id=?")->execute([$no, $id]);

if (isset($in['staff_ids']) && is_array($in['staff_ids'])) {
  $link = db()->prepare("INSERT INTO refusal_staff_links (refusal_id, user_id) VALUES (?,?)");
  foreach ($in['staff_ids'] as $sid) {
    $sid=intval($sid); if ($sid<=0) continue;
    $link->execute([$id,$sid]);
  }
}

audit_log(intval($user['id']), 'CREATE_REFUSAL', 'refusal', $id, ['refusal_no'=>$no]);

header('Content-Type: application/json');
echo json_encode(['ok'=>true,'id'=>$id,'refusal_no'=>$no]);
