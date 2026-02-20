<?php
require_once __DIR__ . '/../app/auth.php';
$user = require_login();
require_perm($user, 'refusals.view');

$month = preg_replace('/[^0-9\-]/','', ($_GET['month'] ?? '')); // YYYY-MM
$q = trim($_GET['q'] ?? '');

$sql = "SELECT r.*, s.name AS created_by_name
        FROM refusals r
        LEFT JOIN staff_users s ON s.id = r.created_by_staff_id";
$where = [];
$params = [];

if ($month) { $where[] = "DATE_FORMAT(r.refusal_date,'%Y-%m') = ?"; $params[] = $month; }
if ($q) {
  $where[] = "(r.refusal_no LIKE ? OR r.location LIKE ? OR r.reason LIKE ?)";
  $params[]='%'.$q.'%'; $params[]='%'.$q.'%'; $params[]='%'.$q.'%';
}

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY r.refusal_date DESC, r.refusal_time DESC, r.id DESC LIMIT 500";

$stmt = db()->prepare($sql);
$stmt->execute($params);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
