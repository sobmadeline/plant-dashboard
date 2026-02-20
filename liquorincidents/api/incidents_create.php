<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
require_once __DIR__ . '/_seq.php';

$user = require_login();
require_perm($user, 'incidents.create');

$in = json_input();
foreach (['incident_date','incident_time','incident_type'] as $r) {
  if (empty($in[$r])) { http_response_code(400); header('Content-Type: application/json'); echo json_encode(['error'=>"Missing $r"]); exit; }
}

$dt = new DateTime($in['incident_date'] . ' 00:00:00');
$no = next_register_no($dt);

$authorities = (isset($in['authorities_notified']) && is_array($in['authorities_notified'])) ? $in['authorities_notified'] : [];
$stmt = db()->prepare("INSERT INTO incidents
  (incident_no, incident_date, incident_time, location, cctv_available, approved_manager_name, reporting_person_name,
   incident_type, authorities_notified_json, police_notified, physical_force, incident_details, actions_taken, soft_locked,
   created_by_staff_id)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?)");
$stmt->execute([
  $no,
  $in['incident_date'],
  $in['incident_time'],
  $in['location'] ?? null,
  array_key_exists('cctv_available',$in) ? normalize_bool($in['cctv_available']) : null,
  $in['approved_manager_name'] ?? ($user['name'] ?? null),
  $in['reporting_person_name'] ?? ($user['name'] ?? null),
  $in['incident_type'],
  $authorities ? json_encode($authorities) : null,
  normalize_bool($in['police_notified'] ?? 0),
  normalize_bool($in['physical_force'] ?? 0),
  $in['incident_details'] ?? null,
  $in['actions_taken'] ?? null,
  intval($user['id']),
]);
$id = intval(db()->lastInsertId());
audit_log(intval($user['id']), 'CREATE', 'incident', $id, null, ['incident_no'=>$no], ['incident_no'=>$no]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true,'id'=>$id,'incident_no'=>$no]);
