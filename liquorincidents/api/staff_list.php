<?php
require_once __DIR__ . '/../app/auth.php';
$user = require_login();
require_perm($user, 'incidents.create');

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
  $stmt = db()->prepare("SELECT id, name, role FROM staff_users WHERE active=1 AND name LIKE ? ORDER BY name LIMIT 50");
  $stmt->execute(['%'.$q.'%']);
} else {
  $stmt = db()->prepare("SELECT id, name, role FROM staff_users WHERE active=1 ORDER BY name LIMIT 50");
  $stmt->execute();
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
