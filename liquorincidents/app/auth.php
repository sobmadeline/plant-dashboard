<?php
require_once __DIR__ . '/db.php';

function role_to_tier(string $roleRaw): string {
  $r = strtolower(trim($roleRaw));

  // TODAY: simple roles
  if ($r === 'admin') return 'admin';
  if ($r === 'staff') return 'staff';

  // FUTURE: managers
  if (str_contains($r, 'manager')) return 'manager';
  if (str_contains($r, 'approved')) return 'manager';
  if (str_contains($r, 'duty')) return 'manager';

  // FUTURE: operational roles (still "staff tier" by default)
  if (str_contains($r, 'lifeguard')) return 'staff';
  if (str_contains($r, 'facility')) return 'staff';
  if (str_contains($r, 'attendant')) return 'staff';

  // Default safety
  return 'staff';
}

function require_login(): array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();

  // BRAC Tools current login stores:
  // $_SESSION['staff'] = ['id'=>..., 'name'=>..., 'role'=>...]
  if (isset($_SESSION['staff']) && is_array($_SESSION['staff']) && isset($_SESSION['staff']['id'])) {
    $role = role_to_tier((string)($_SESSION['staff']['role'] ?? 'staff'));
    return [
      'id' => intval($_SESSION['staff']['id']),
      'display_name' => (string)($_SESSION['staff']['name'] ?? 'Unknown'),
      'role' => $role,
    ];
  }

  // If your BRAC Tools later standardises this, we will also accept:
  // $_SESSION['user'] = ['id'=>..., 'display_name'=>..., 'role'=>...]
  if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    return $_SESSION['user'];
  }

  http_response_code(401);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Unauthorised']);
  exit;
}

function require_role(string $minRole, array $user): void {
  $rank = ['staff'=>1,'manager'=>2,'admin'=>3];
  $uRank = $rank[$user['role']] ?? 0;
  $mRank = $rank[$minRole] ?? 999;
  if ($uRank < $mRank) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
  }
}

function client_meta(): array {
  return [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
  ];
}

function audit_log(?int $userId, string $action, string $entityType, ?int $entityId, ?array $meta=null): void {
  $m = client_meta();
  $stmt = db()->prepare("INSERT INTO audit_log (user_id, action, entity_type, entity_id, meta_json, ip_addr, user_agent)
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $userId,
    $action,
    $entityType,
    $entityId,
    $meta ? json_encode($meta) : null,
    $m['ip'],
    $m['ua'],
  ]);
}
