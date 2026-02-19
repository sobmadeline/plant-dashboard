<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/util.php';

$user = require_login();
require_role('manager', $user);

$cfg = get_config();
$id = isset($_POST['incident_id']) ? intval($_POST['incident_id']) : 0;
if ($id <= 0) { http_response_code(400); echo "Missing incident_id"; exit; }

if (!isset($_FILES['file'])) { http_response_code(400); echo "Missing file"; exit; }
$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo "Upload failed"; exit; }
if ($f['size'] > $cfg['max_upload_bytes']) { http_response_code(400); echo "File too large"; exit; }

$tmp = $f['tmp_name'];
$mime = mime_content_type($tmp) ?: 'application/octet-stream';
if (!in_array($mime, $cfg['allowed_mimes'], true)) { http_response_code(400); echo "File type not allowed"; exit; }

$orig = safe_filename($f['name']);
$dir = rtrim($cfg['upload_dir'], '/\\') . '/' . $id;
if (!is_dir($dir)) mkdir($dir, 0775, true);

$ext = pathinfo($orig, PATHINFO_EXTENSION);
$stored = bin2hex(random_bytes(16)) . ($ext ? ('.'.$ext) : '');
$path = $dir . '/' . $stored;

if (!move_uploaded_file($tmp, $path)) { http_response_code(500); echo "Could not store file"; exit; }

$sha = compute_sha256($path);

$stmt = db()->prepare("INSERT INTO incident_files
  (incident_id, uploaded_by_user_id, original_name, stored_name, mime_type, file_size, sha256, storage_path)
  VALUES (?,?,?,?,?,?,?,?)");
$stmt->execute([$id, intval($user['id']), $orig, $stored, $mime, intval($f['size']), $sha, $path]);

$fileId = intval(db()->lastInsertId());
audit_log(intval($user['id']), 'UPLOAD_FILE', 'incident', $id, ['file_id'=>$fileId, 'mime'=>$mime, 'size'=>$f['size']]);

header('Content-Type: application/json');
echo json_encode(['ok'=>true,'file_id'=>$fileId,'name'=>$orig]);
