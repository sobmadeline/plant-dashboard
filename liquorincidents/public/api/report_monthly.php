<?php
require_once __DIR__ . '/../../app/auth.php';

$user = require_login();
require_role('manager', $user);

$month = $_GET['month'] ?? ''; // YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
  http_response_code(400);
  echo json_encode(['error'=>'month must be YYYY-MM']);
  exit;
}
$from = $month . "-01";
$to = date('Y-m-t', strtotime($from));

$inc = db()->prepare("SELECT incident_type, COUNT(*) as cnt,
                             SUM(police_notified) as police_cnt,
                             SUM(physical_force) as force_cnt
                      FROM incidents
                      WHERE incident_date BETWEEN ? AND ?
                      GROUP BY incident_type
                      ORDER BY cnt DESC");
$inc->execute([$from, $to]);

$ref = db()->prepare("SELECT reason, COUNT(*) as cnt, SUM(police_notified) as police_cnt
                      FROM refusals
                      WHERE refusal_date BETWEEN ? AND ?
                      GROUP BY reason
                      ORDER BY cnt DESC");
$ref->execute([$from, $to]);

$out = [
  'ok'=>true,
  'month'=>$month,
  'range'=>['from'=>$from,'to'=>$to],
  'incidents'=>$inc->fetchAll(),
  'refusals'=>$ref->fetchAll(),
];

audit_log(intval($user['id']), 'EXPORT_REPORT', 'report', null, ['month'=>$month]);

header('Content-Type: application/json');
echo json_encode($out);
