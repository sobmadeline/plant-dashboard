<?php
// Copy to config.php and set credentials.
return [
  'db' => [
    'host' => 'localhost',
    'name' => 'brac_tools',
    'user' => 'brac_user',
    'pass' => 'CHANGE_ME',
    'charset' => 'utf8mb4',
  ],
  // Where uploaded incident files are stored on disk (must be writable by PHP).
  'upload_dir' => __DIR__ . '/../uploads/incidents',
  // Base URL path where this module is served from (adjust if in subfolder)
  'base_path' => '/incidents',
  // Max upload size in bytes (default 10MB)
  'max_upload_bytes' => 10 * 1024 * 1024,
  // Allowed mime types for uploads
  'allowed_mimes' => [
    'image/jpeg',
    'image/png',
    'image/webp',
    'application/pdf',
  ],
];
