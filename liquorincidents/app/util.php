<?php
function json_input(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $data = json_decode($raw, true);
  if (!is_array($data)) return [];
  return $data;
}

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function make_incident_no(string $prefix, string $dateYmd, int $id): string {
  // e.g. INC-20260219-000123
  return sprintf("%s-%s-%06d", $prefix, $dateYmd, $id);
}

function normalize_bool($v): int {
  if ($v === true || $v === 1 || $v === "1" || $v === "true" || $v === "on" || $v === "yes") return 1;
  return 0;
}

function compute_sha256(string $path): string {
  return hash_file('sha256', $path);
}

function safe_filename(string $name): string {
  $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
  $name = trim($name, "._-");
  return $name ?: 'file';
}

function diff_assoc(array $before, array $after): array {
  $diff = [];
  $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
  foreach ($keys as $k) {
    $b = $before[$k] ?? null;
    $a = $after[$k] ?? null;
    if ($b !== $a) $diff[$k] = ['from'=>$b, 'to'=>$a];
  }
  return $diff;
}
