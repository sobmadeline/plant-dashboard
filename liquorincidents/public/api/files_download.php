<?php
require_once __DIR__ . '/../../app/auth.php';

$user = require_login();
require_role('manager', $user);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { http_response_code(400); echo "Missing id"; exit; }

$stmt = db()->prepare("SELECT * FROM incident_files WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo "Not found"; exit; }

$path = $row['storage_path'];
if (!file_exists($path)) { http_response_code(404); echo "Missing file"; exit; }

header('Content-Type: ' . $row['mime_type']);
header('Content-Length: ' . $row['file_size']);
header('Content-Disposition: attachment; filename="' . $row['original_name'] . '"');
readfile($path);
