<?php
session_start();

/**
 * If a script sets $GLOBALS['REQUIRE_ADMIN']=true before including this file,
 * we enforce admin role.
 */
$require_admin = !empty($GLOBALS['REQUIRE_ADMIN']);

if (!isset($_SESSION['staff'])) {
  http_response_code(401);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>false,'error'=>'Not authenticated']);
  exit;
}

if ($require_admin && (($_SESSION['staff']['role'] ?? '') !== 'admin')) {
  http_response_code(403);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>false,'error'=>'Admin required']);
  exit;
}

/**
 * IMPORTANT:
 * If this file is being called directly (opened in browser), return JSON.
 * If it's being included by another endpoint, stay silent and let the caller respond.
 */
$called_directly = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'auth_check.php');

if ($called_directly) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok'=>true,'user'=>$_SESSION['staff']]);
}
