<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';

function require_login(): array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();

  if (isset($_SESSION['staff']) && is_array($_SESSION['staff']) && isset($_SESSION['staff']['id'])) {
    $roleKey = strtolower((string)($_SESSION['staff']['role'] ?? 'staff'));
    return [
      'id' => intval($_SESSION['staff']['id']),
      'name' => (string)($_SESSION['staff']['name'] ?? 'Unknown'),
      'role' => $roleKey ?: 'staff',
    ];
  }

  http_response_code(401);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error'=>'Unauthorised']);
  exit;
}

function has_perm(array $user, string $permKey): bool {
  $stmt = db()->prepare("SELECT effect FROM user_permissions WHERE staff_id=? AND perm_key=?");
  $stmt->execute([intval($user['id']), $permKey]);
  $row = $stmt->fetch();
  if ($row) return $row['effect'] === 'allow';

  if (($user['role'] ?? '') === 'admin') return true;

  $stmt = db()->prepare("SELECT allowed FROM role_permissions WHERE role_key=? AND perm_key=?");
  $stmt->execute([$user['role'], $permKey]);
  $rp = $stmt->fetch();
  return $rp ? intval($rp['allowed']) === 1 : false;
}

function require_perm(array $user, string $permKey): void {
  if (!has_perm($user, $permKey)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>'Forbidden']);
    exit;
  }
}
