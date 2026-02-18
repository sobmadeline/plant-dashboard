<?php
require_once __DIR__ . '/db.php';
header('Content-Type: text/plain; charset=utf-8');

$name = "Admin";
$pin  = "1234"; // CHANGE THIS
$hash = password_hash($pin, PASSWORD_DEFAULT);

db()->prepare("INSERT INTO staff_users (name, pin_hash, role, active) VALUES (?,?, 'admin', 1)")
   ->execute([$name, $hash]);

echo "Admin created. DELETE this file now.\n";
