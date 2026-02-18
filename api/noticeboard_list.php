<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $sql = "SELECT id, title, message, category, start_at, end_at, email_to
          FROM noticeboard
          WHERE (start_at IS NULL OR start_at <= NOW())
            AND (end_at IS NULL OR end_at >= NOW())
          ORDER BY start_at DESC, id DESC";
  $rows = db()->query($sql)->fetchAll();

  echo json_encode(['ok' => true, 'rows' => $rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
