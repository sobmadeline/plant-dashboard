<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $user = require_login();

  $in = json_input();
  $required = ['incident_date','incident_time','incident_type'];
  foreach ($required as $r) {
    if (empty($in[$r])) { http_response_code(400); echo json_encode(['error'=>"Missing $r"]); exit; }
  }

  $stmt = db()->prepare("INSERT INTO incidents
    (incident_no, incident_date, incident_time, location, cctv_available, approved_manager_name, reporting_person_name,
     incident_type, authorities_notified_json, police_notified, physical_force, incident_details, actions_taken, soft_locked,
     created_by_staff_id)
    VALUES ('', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");

  $authorities = isset($in['authorities_notified']) && is_array($in['authorities_notified']) ? $in['authorities_notified'] : [];
  $stmt->execute([
    $in['incident_date'],
    $in['incident_time'],
    $in['location'] ?? null,
    isset($in['cctv_available']) ? normalize_bool($in['cctv_available']) : null,
    $in['approved_manager_name'] ?? ($user['display_name'] ?? null),
    $in['reporting_person_name'] ?? ($user['display_name'] ?? null),
    $in['incident_type'],
    $authorities ? json_encode($authorities) : null,
    normalize_bool($in['police_notified'] ?? 0),
    normalize_bool($in['physical_force'] ?? 0),
    $in['incident_details'] ?? null,
    $in['actions_taken'] ?? null,
    intval($user['id']),
  ]);

  $id = intval(db()->lastInsertId());
  $no = make_incident_no('INC', str_replace('-','', $in['incident_date']), $id);
  db()->prepare("UPDATE incidents SET incident_no=? WHERE id=?")->execute([$no, $id]);

  // staff links
  if (isset($in['staff_ids']) && is_array($in['staff_ids'])) {
    $link = db()->prepare("INSERT INTO incident_staff_links (incident_id, staff_id, role_in_incident) VALUES (?,?,?)");
    foreach ($in['staff_ids'] as $sid) {
      $sid = intval($sid);
      if ($sid <= 0) continue;
      $link->execute([$id, $sid, 'Employee']);
    }
  }

  audit_log(intval($user['id']), 'CREATE_INCIDENT', 'incident', $id, ['incident_no'=>$no]);

  echo json_encode(['ok'=>true,'id'=>$id,'incident_no'=>$no]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => $e->getMessage(),
    'file' => basename($e->getFile()),
    'line' => $e->getLine()
  ]);
}
