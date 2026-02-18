<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code=400){
  http_response_code($code);
  echo json_encode(['ok' => false, 'error' => $msg]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  fail('POST required', 405);
}

// Accept JSON or form-encoded
$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
  $json = json_decode($raw, true);
  if (is_array($json)) $data = $json;
}
if (!$data) $data = $_POST;

$component_id = isset($data['component_id']) ? trim((string)$data['component_id']) : '';
if ($component_id === '') fail('component_id is required');

$allowedStatus = ['Operational','Degraded','Offline','Planned Maintenance'];

$fields = [
  'status' => null,
  'condition_name' => null,
  'priority' => null,
  'issue_summary' => null,
  'owner' => null,
  'notes' => null,
  'status_order' => null,
];

$set = [];
$params = [':component_id' => $component_id];

foreach ($fields as $k => $_) {
  if (!array_key_exists($k, $data)) continue;

  $v = $data[$k];

  if ($k === 'status') {
    $v = trim((string)$v);
    if ($v !== '' && !in_array($v, $allowedStatus, true)) {
      fail('Invalid status');
    }
  }

  if ($k === 'status_order') {
    $v = ($v === '' || $v === null) ? null : (int)$v;
  } else {
    $v = ($v === null) ? null : trim((string)$v);
    if ($v === '') $v = null;
  }

  $set[] = "$k = :$k";
  $params[":$k"] = $v;
}

if (!$set) fail('No updatable fields provided');

$set[] = "last_updated = UTC_TIMESTAMP()";

$sql = "UPDATE plant_status SET " . implode(", ", $set) . " WHERE component_id = :component_id LIMIT 1";

try {
  $stmt = db()->prepare($sql);
  $stmt->execute($params);

  echo json_encode([
    'ok' => true,
    'updated' => $stmt->rowCount(),
    'component_id' => $component_id
  ]);
} catch (Throwable $e) {
  fail($e->getMessage(), 500);
}
