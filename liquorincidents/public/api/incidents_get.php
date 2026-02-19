<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

$user = require_login();
// viewing register should be manager+ (staff can still view their own incident via id, but keep it simple here)
require_role('manager', $user);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
  $stmt = db()->prepare("SELECT * FROM incidents WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

  $staff = db()->prepare("SELECT isl.user_id, u.display_name, isl.role_in_incident
                          FROM incident_staff_links isl JOIN users u ON u.id=isl.user_id
                          WHERE isl.incident_id=?");
  $staff->execute([$id]);
  $row['staff'] = $staff->fetchAll();

  $files = db()->prepare("SELECT id, original_name, mime_type, file_size, uploaded_at FROM incident_files WHERE incident_id=? ORDER BY id DESC");
  $files->execute([$id]);
  $row['files'] = $files->fetchAll();

  header('Content-Type: application/json');
  echo json_encode(['ok'=>true,'incident'=>$row]);
  exit;
}

// list with filters
$where = [];
$args = [];
if (!empty($_GET['from'])) { $where[] = "incident_date >= ?"; $args[] = $_GET['from']; }
if (!empty($_GET['to'])) { $where[] = "incident_date <= ?"; $args[] = $_GET['to']; }
if (!empty($_GET['type'])) { $where[] = "incident_type = ?"; $args[] = $_GET['type']; }
if (isset($_GET['police']) && $_GET['police'] !== '') { $where[] = "police_notified = ?"; $args[] = intval($_GET['police']); }

$sql = "SELECT id, incident_no, incident_date, incident_time, location, incident_type, police_notified, physical_force, created_at
        FROM incidents";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY incident_date DESC, incident_time DESC LIMIT 500";

$stmt = db()->prepare($sql);
$stmt->execute($args);
$rows = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode(['ok'=>true,'items'=>$rows]);
