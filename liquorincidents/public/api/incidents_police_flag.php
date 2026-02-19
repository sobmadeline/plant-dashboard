<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

$user = require_login();
require_role('manager', $user);

$in = json_input();
$id = intval($in['id'] ?? 0);
$val = normalize_bool($in['police_notified'] ?? 1);
if ($id <= 0) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

db()->prepare("UPDATE incidents SET police_notified=? WHERE id=?")->execute([$val, $id]);
audit_log(intval($user['id']), 'POLICE_FLAG', 'incident', $id, ['police_notified'=>$val]);

echo json_encode(['ok'=>true]);
