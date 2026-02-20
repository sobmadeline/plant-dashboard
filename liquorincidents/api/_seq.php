<?php
require_once __DIR__ . '/../app/db.php';
function next_register_no(DateTime $dt): string {
  $yyyymm = $dt->format('Ym');
  $pdo = db();
  $pdo->beginTransaction();
  try {
    $stmt = $pdo->prepare("SELECT last_num FROM register_sequences WHERE yyyymm=? FOR UPDATE");
    $stmt->execute([$yyyymm]);
    $row = $stmt->fetch();
    if (!$row) { $pdo->prepare("INSERT INTO register_sequences (yyyymm, last_num) VALUES (?, 0)")->execute([$yyyymm]); $last=0; }
    else { $last=intval($row['last_num']); }
    $next=$last+1;
    $pdo->prepare("UPDATE register_sequences SET last_num=? WHERE yyyymm=?")->execute([$next,$yyyymm]);
    $pdo->commit();
    return sprintf("INC-%s-%06d", $yyyymm, $next);
  } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
}
