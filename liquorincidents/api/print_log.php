<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/util.php';
$user = require_login();

$in = json_input();
$what = (string)($in['what'] ?? '');
$meta = $in['meta'] ?? null;

audit_log(intval($user['id']), 'PRINT', 'register', null, null, null, ['what'=>$what,'meta'=>$meta]);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>true]);
