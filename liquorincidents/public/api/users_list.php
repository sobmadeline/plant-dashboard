<?php
require_once __DIR__ . '/../../app/auth.php';

$user = require_login();
require_role('manager', $user);

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $stmt = db()->prepare("SELECT id, display_name, role FROM users WHERE is_active=1 AND display_name LIKE ? ORDER BY display_name LIMIT 50");
  $stmt->execute(['%'.$q.'%']);
} else {
  $stmt = db()->prepare("SELECT id, display_name, role FROM users WHERE is_active=1 ORDER BY display_name LIMIT 50");
  $stmt->execute();
}
echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
