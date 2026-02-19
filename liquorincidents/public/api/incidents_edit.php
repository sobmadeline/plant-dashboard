<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

$user = require_login();
require_role('manager', $user);

$in = json_input();
$id = intval($in['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

$stmt = db()->prepare("SELECT * FROM incidents WHERE id=?");
$stmt->execute([$id]);
$before = $stmt->fetch();
if (!$before) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

$edit_reason = trim((string)($in['edit_reason'] ?? ''));
if ($edit_reason === '') $edit_reason = 'Edit made via incident register';

$after = $before;
$fields = [
  'incident_date','incident_time','location','cctv_available','approved_manager_name','reporting_person_name',
  'incident_type','police_notified','physical_force','incident_details','actions_taken'
];

foreach ($fields as $f) {
  if (array_key_exists($f, $in)) {
    $val = $in[$f];
    if (in_array($f, ['police_notified','physical_force'])) $val = normalize_bool($val);
    if ($f === 'cctv_available') $val = ($val === null ? null : normalize_bool($val));
    $after[$f] = $val;
  }
}
if (array_key_exists('authorities_notified', $in)) {
  $after['authorities_notified_json'] = (is_array($in['authorities_notified']) && $in['authorities_notified'])
    ? json_encode($in['authorities_notified']) : null;
}

$beforeSnap = [
  'incident_date'=>$before['incident_date'],
  'incident_time'=>$before['incident_time'],
  'location'=>$before['location'],
  'cctv_available'=>$before['cctv_available'],
  'approved_manager_name'=>$before['approved_manager_name'],
  'reporting_person_name'=>$before['reporting_person_name'],
  'incident_type'=>$before['incident_type'],
  'authorities_notified'=>$before['authorities_notified_json'] ? json_decode($before['authorities_notified_json'], true) : [],
  'police_notified'=>intval($before['police_notified']),
  'physical_force'=>intval($before['physical_force']),
  'incident_details'=>$before['incident_details'],
  'actions_taken'=>$before['actions_taken'],
];

$afterSnap = [
  'incident_date'=>$after['incident_date'],
  'incident_time'=>$after['incident_time'],
  'location'=>$after['location'],
  'cctv_available'=>$after['cctv_available'],
  'approved_manager_name'=>$after['approved_manager_name'],
  'reporting_person_name'=>$after['reporting_person_name'],
  'incident_type'=>$after['incident_type'],
  'authorities_notified'=>$after['authorities_notified_json'] ? json_decode($after['authorities_notified_json'], true) : [],
  'police_notified'=>intval($after['police_notified']),
  'physical_force'=>intval($after['physical_force']),
  'incident_details'=>$after['incident_details'],
  'actions_taken'=>$after['actions_taken'],
];

$diff = diff_assoc($beforeSnap, $afterSnap);
if (!$diff) { echo json_encode(['ok'=>true,'no_change'=>true]); exit; }

$upd = db()->prepare("UPDATE incidents SET
  incident_date=?, incident_time=?, location=?, cctv_available=?, approved_manager_name=?, reporting_person_name=?,
  incident_type=?, authorities_notified_json=?, police_notified=?, physical_force=?, incident_details=?, actions_taken=?
  WHERE id=?");
$upd->execute([
  $after['incident_date'], $after['incident_time'], $after['location'], $after['cctv_available'],
  $after['approved_manager_name'], $after['reporting_person_name'],
  $after['incident_type'], $after['authorities_notified_json'],
  intval($after['police_notified']), intval($after['physical_force']),
  $after['incident_details'], $after['actions_taken'],
  $id
]);

$rev = db()->prepare("INSERT INTO incident_revisions
  (incident_id, edited_by_user_id, edit_reason, before_json, after_json, diff_json)
  VALUES (?,?,?,?,?,?)");
$rev->execute([
  $id,
  intval($user['id']),
  $edit_reason,
  json_encode($beforeSnap),
  json_encode($afterSnap),
  json_encode($diff),
]);

audit_log(intval($user['id']), 'EDIT_INCIDENT', 'incident', $id, ['reason'=>$edit_reason, 'diff_keys'=>array_keys($diff)]);

header('Content-Type: application/json');
echo json_encode(['ok'=>true,'diff'=>$diff]);
