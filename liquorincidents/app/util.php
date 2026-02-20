<?php
function json_input(): array {
  $raw = file_get_contents('php://input');
  if ($raw === false || trim($raw) === '') return [];
  $j = json_decode($raw, true);
  return is_array($j) ? $j : [];
}

function normalize_bool($v): int {
  if (is_bool($v)) return $v ? 1 : 0;
  if (is_numeric($v)) return intval($v) ? 1 : 0;
  $s = strtolower(trim((string)$v));
  return in_array($s, ['1','true','yes','y','on'], true) ? 1 : 0;
}

function client_meta(): array {
  return [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
  ];
}

function audit_log(?int $actorStaffId, string $action, string $entityType, $entityId=null, $before=null, $after=null, $meta=null): void {
  $m = client_meta();
  $stmt = db()->prepare("INSERT INTO audit_log (actor_staff_id, action, entity_type, entity_id, before_json, after_json, meta_json, ip_addr, user_agent)
                         VALUES (?,?,?,?,?,?,?,?,?)");
  $stmt->execute([
    $actorStaffId,
    $action,
    $entityType,
    $entityId,
    $before !== null ? json_encode($before) : null,
    $after !== null ? json_encode($after) : null,
    $meta !== null ? json_encode($meta) : null,
    $m['ip'],
    $m['ua'],
  ]);
}

function diff_assoc(array $before, array $after): array {
  $diff = [];
  $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
  foreach ($keys as $k) {
    $bv = $before[$k] ?? null;
    $av = $after[$k] ?? null;
    if ($bv !== $av) $diff[$k] = ['from'=>$bv,'to'=>$av];
  }
  return $diff;
}

function rand_name(string $ext=''): string {
  $b = bin2hex(random_bytes(16));
  return $ext ? ($b . '.' . $ext) : $b;
}
