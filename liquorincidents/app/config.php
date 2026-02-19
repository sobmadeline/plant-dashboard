<?php
/**
 * Incident module config (BRAC Tools compatible)
 *
 * This file is safe to include inside /incidents/app/ and DOES NOT replace your existing root config.php.
 *
 * It reads DB constants from your existing BRAC Tools config.php:
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 *
 * Upload settings are configured below.
 */
$rootConfig = dirname(__DIR__) . '/../config.php'; // adjust if your root config.php is elsewhere
if (file_exists($rootConfig)) {
  require_once $rootConfig;
}

return [
  'db' => [
    'host' => defined('DB_HOST') ? DB_HOST : 'localhost',
    'port' => defined('DB_PORT') ? DB_PORT : 3306,
    'name' => defined('DB_NAME') ? DB_NAME : 'bractools',
    'user' => defined('DB_USER') ? DB_USER : 'root',
    'pass' => defined('DB_PASS') ? DB_PASS : '',
    'charset' => 'utf8mb4',
  ],
  // Where uploaded incident files are stored on disk (must be writable by PHP).
  'upload_dir' => __DIR__ . '/../uploads/incidents',
  // Base URL path where this module is served from (adjust if in subfolder)
  'base_path' => '/incidents',
  // Max upload size in bytes
  'max_upload_bytes' => 20 * 1024 * 1024, // 20MB default
  // Allowed mime types for uploads
  'allowed_mimes' => [
    'image/jpeg',
    'image/png',
    'image/webp',
    'application/pdf',
  ],
];
