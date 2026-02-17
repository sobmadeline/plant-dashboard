<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
  // Optional query params: ?q=search&status=Open&priority=High
  $q = isset($_GET['q']) ? trim($_GET['q']) : '';
  $status = isset($_GET['status']) ? trim($_GET['status']) : '';
  $priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';

  $where = [];
  $params = [];

  if ($q !== '') {
    $where[] = "(component_id LIKE :q OR component_name LIKE :q OR system_name LIKE :q OR location_name LIKE :q OR issue_summary LIKE :q OR owner LIKE :q OR notes LIKE :q)";
    $params[':q'] = '%' . $q . '%';
  }
  if ($status !== '') {
    $where[] = "status = :status";
    $params[':status'] = $status;
  }
  if ($priority !== '') {
    $where[] = "priority = :priority";
    $params[':priority'] = $priority;
  }

  $sql = "SELECT
            id, component_id, component_name, system_name, location_name,
            status, condition_name, priority, issue_summary, owner,
            last_updated, notes, status_order
          FROM plant_status";

  if ($where) $sql .= " WHERE " . implode(" AND ", $where);

  // Sort: status_order first (NULLs last), then last_updated desc, then name
  $sql .= " ORDER BY (status_order IS NULL), status_order ASC, last_updated DESC, component_name ASC";

  $stmt = db()->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll();

  echo json_encode(['ok' => true, 'rows' => $rows], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
