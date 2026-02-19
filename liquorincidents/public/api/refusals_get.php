<?php
require_once __DIR__ . '/../../app/auth.php';

$user = require_login();
require_role('manager', $user);

$where=[]; $args=[];
if (!empty($_GET['from'])) { $where[]="refusal_date >= ?"; $args[]=$_GET['from']; }
if (!empty($_GET['to'])) { $where[]="refusal_date <= ?"; $args[]=$_GET['to']; }
if (isset($_GET['police']) && $_GET['police']!=='') { $where[]="police_notified = ?"; $args[]=intval($_GET['police']); }

$sql="SELECT id, refusal_no, refusal_date, refusal_time, location, reason, police_notified, created_at FROM refusals";
if ($where) $sql.=" WHERE ".implode(" AND ",$where);
$sql.=" ORDER BY refusal_date DESC, refusal_time DESC LIMIT 500";

$stmt=db()->prepare($sql);
$stmt->execute($args);
echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
