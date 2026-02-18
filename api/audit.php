<?php
require_once __DIR__ . '/db.php';

function audit_log(string $action, string $table_name, ?int $record_id, $before=null, $after=null): void {
  try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $staff = $_SESSION['staff'] ?? null;

    $user_id = $staff['id'] ?? null;
    $user_name = $staff['name'] ?? null;
    $role = $staff['role'] ?? null;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $before_json = is_null($before) ? null : json_encode($before, JSON_UNESCAPED_UNICODE);
    $after_json  = is_null($after)  ? null : json_encode($after,  JSON_UNESCAPED_UNICODE);

    $stmt = db()->prepare("INSERT INTO audit_log
      (user_id, user_name, role, action, table_name, record_id, before_json, after_json, ip, user_agent)
      VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
      $user_id, $user_name, $role, $action, $table_name, $record_id,
      $before_json, $after_json, $ip, $ua
    ]);
  } catch (Throwable $e) {
    // Never block main action if audit logging fails
  }
}
