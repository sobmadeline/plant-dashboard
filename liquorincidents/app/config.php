<?php
$rootConfig = dirname(__DIR__) . '/../config.php';
if (file_exists($rootConfig)) require_once $rootConfig;

return [
  'db' => [
    'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
    'port' => defined('DB_PORT') ? DB_PORT : 3306,
    'name' => defined('DB_NAME') ? DB_NAME : 'bractools',
    'user' => defined('DB_USER') ? DB_USER : 'root',
    'pass' => defined('DB_PASS') ? DB_PASS : '',
    'charset' => 'utf8mb4',
  ],
  'upload_dir' => __DIR__ . '/../uploads',
  'base_path' => '/liquorincidents',
  'max_upload_bytes' => 25 * 1024 * 1024,
  'allowed_mimes' => ['image/jpeg','image/png','image/webp','application/pdf'],
];
