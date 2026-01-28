<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
$pdo->exec("CREATE TABLE IF NOT EXISTS facilities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  type VARCHAR(100) NOT NULL,
  location VARCHAR(255) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_facilities_name (name),
  INDEX idx_facilities_type (type),
  INDEX idx_facilities_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';
function json_out($data,$code=200){ http_response_code($code); echo json_encode($data); exit; }
try{
  if($method==='GET' && $action==='export'){
    header_remove('Content-Type'); header('Content-Type:text/csv');
    header('Content-Disposition: attachment; filename="facilities.csv"');
    $out=fopen('php://output','w'); fputcsv($out,['id','name','type','location','notes','created_at','updated_at']);
    $st=$pdo->query("SELECT id,name,type,location,notes,created_at,updated_at FROM facilities ORDER BY name ASC");
    while($r=$st->fetch(PDO::FETCH_ASSOC)){ fputcsv($out,$r); } fclose($out); exit;
  }
  if($method==='GET'){
    $q=trim($_GET['q']??''); 
    if($q!==''){ $st=$pdo->prepare("SELECT * FROM facilities WHERE name LIKE ? OR type LIKE ? OR location LIKE ? ORDER BY name ASC"); $like='%'.$q.'%'; $st->execute([$like,$like,$like]); }
    else { $st=$pdo->query("SELECT * FROM facilities ORDER BY name ASC"); }
    json_out(['ok'=>true,'items'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
  }
  $raw=file_get_contents('php://input'); $input=json_decode($raw,true); if(!is_array($input)){$input=$_POST;}
  if($method==='POST'){
    $id=intval($_GET['id']??($input['id']??0));
    if(($action==='update')||$id>0){
      if($id<=0) json_out(['ok'=>false,'error'=>'Missing id'],400);
      $name=trim($input['name']??''); $type=trim($input['type']??''); $location=trim($input['location']??''); $notes=trim($input['notes']??'');
      if($name===''||$type==='') json_out(['ok'=>false,'error'=>'Name and type are required.'],400);
      $st=$pdo->prepare("UPDATE facilities SET name=?,type=?,location=?,notes=? WHERE id=?");
      $st->execute([$name,$type,$location?:null,$notes?:null,$id]); json_out(['ok'=>true]);
    } else {
      $name=trim($input['name']??''); $type=trim($input['type']??''); $location=trim($input['location']??''); $notes=trim($input['notes']??'');
      if($name===''||$type==='') json_out(['ok'=>false,'error'=>'Name and type are required.'],400);
      $st=$pdo->prepare("INSERT INTO facilities (name,type,location,notes,created_by) VALUES (?,?,?,?,?)");
      $st->execute([$name,$type,$location?:null,$notes?:null,$_SESSION['user_id']??null]); json_out(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    }
  }
  if($method==='PUT'){
    $id=intval($_GET['id']??($input['id']??0)); if($id<=0) json_out(['ok'=>false,'error'=>'Missing id'],400);
    $name=trim($input['name']??''); $type=trim($input['type']??''); $location=trim($input['location']??''); $notes=trim($input['notes']??'');
    if($name===''||$type==='') json_out(['ok'=>false,'error'=>'Name and type are required.'],400);
    $st=$pdo->prepare("UPDATE facilities SET name=?,type=?,location=?,notes=? WHERE id=?");
    $st->execute([$name,$type,$location?:null,$notes?:null,$id]); json_out(['ok'=>true]);
  }
  if($method==='DELETE'){
    $id=intval($_GET['id']??0); if($id<=0){ $payload=json_decode(file_get_contents('php://input'),true); $id=intval($payload['id']??0); }
    if($id<=0) json_out(['ok'=>false,'error'=>'Missing id'],400);
    $st=$pdo->prepare("DELETE FROM facilities WHERE id=?"); $st->execute([$id]); json_out(['ok'=>true]);
  }
  json_out(['ok'=>false,'error'=>'Unsupported request'],405);
}catch(Throwable $e){ json_out(['ok'=>false,'error'=>$e->getMessage()],500); }
