<?php
require_once __DIR__ . '/../../app/auth.php';
$user = require_login();
require_role('manager', $user);

$q = trim($_GET['q'] ?? '');

$sql = "SELECT id, name AS display_name, role
        FROM staff_users
        WHERE is_active = 1
          AND (name LIKE :q OR role LIKE :q)
        ORDER BY name
        LIMIT 20";

$stmt = db()->prepare($sql);
$stmt->execute([':q' => "%$q%"]);
$rows = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode(['ok'=>true, 'results'=>$rows]);
