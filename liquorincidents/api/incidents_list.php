<?php
require_once __DIR__ . '/../app/auth.php';
$user=require_login();require_perm($user,'incidents.view');
$month=preg_replace('/[^0-9\-]/','',($_GET['month']??''));$q=trim($_GET['q']??'');
$sql="SELECT i.*, s.name AS created_by_name FROM incidents i LEFT JOIN staff_users s ON s.id=i.created_by_staff_id";
$where=[];$params=[];
if($month){$where[]="DATE_FORMAT(i.incident_date,'%Y-%m') = ?";$params[]=$month;}
if($q){$where[]="(i.incident_no LIKE ? OR i.location LIKE ? OR i.incident_type LIKE ?)";$params[]='%'.$q.'%';$params[]='%'.$q.'%';$params[]='%'.$q.'%';}
if($where)$sql.=" WHERE ".implode(" AND ",$where);
$sql.=" ORDER BY i.incident_date DESC,i.incident_time DESC,i.id DESC LIMIT 500";
$stmt=db()->prepare($sql);$stmt->execute($params);
header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'items'=>$stmt->fetchAll()]);
