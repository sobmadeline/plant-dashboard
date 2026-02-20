<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $user = require_login();
  require_perm($user, 'admin.permissions.manage');

  $in = json_input();
  $staffIds = (isset($in['staff_ids']) && is_array($in['staff_ids'])) ? $in['staff_ids'] : [];
  if (!$staffIds) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing staff_ids']); exit; }

  $clean = [];
  foreach ($staffIds as $sid) { $sid = intval($sid); if ($sid > 0) $clean[] = $sid; }
  if (!$clean) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'No valid staff_ids']); exit; }

  $inClause = implode(',', array_fill(0, count($clean), '?'));

  $before = db()->prepare("SELECT COUNT(*) AS c FROM user_permissions WHERE staff_id IN ($inClause)");
  $before->execute($clean);
  $bc = intval(($before->fetch())['c'] ?? 0);

  $del = db()->prepare("DELETE FROM user_permissions WHERE staff_id IN ($inClause)");
  $del->execute($clean);

  audit_log(intval($user['id']), 'PERM_USER_RESET', 'user_permission', null, ['count'=>$bc], ['count'=>0], ['staff_ids'=>$clean]);

  echo json_encode(['ok'=>true,'deleted'=>$bc]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => $e->getMessage(),
    'where' => $e->getFile() . ':' . $e->getLine()
  ]);
}