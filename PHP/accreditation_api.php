<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok,$data=null,$error=null){ echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error]); exit; }

try{
  // Ensure mapping table
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS program_accreditation (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      program_id INT UNSIGNED NOT NULL,
      level VARCHAR(32) NOT NULL DEFAULT 'Candidate',
      phase VARCHAR(32) DEFAULT NULL,
      status VARCHAR(16) NOT NULL DEFAULT 'active',
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY(id),
      UNIQUE KEY uq_program (program_id),
      CONSTRAINT fk_prog_acc_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");
}catch(Throwable $e){ jexit(false,null,'DB error: '.$e->getMessage()); }

$method = $_SERVER['REQUEST_METHOD'];

if($method==='GET'){
  $stmt = $pdo->query("
    SELECT 
  p.id AS program_id, 
  p.code, 
  p.name,
  /* Highest-weight tagged Level for this program (from levels + level_programs) */
  (
    SELECT SUBSTRING_INDEX(
             GROUP_CONCAT(l.name ORDER BY l.weight DESC, l.id DESC SEPARATOR '||'),
             '||', 1
           )
    FROM level_programs lp
    JOIN levels l ON l.id = lp.level_id
    WHERE lp.program_id = p.id
  ) AS level,
  a.phase, 
  a.status, 
  a.updated_at
FROM programs p
LEFT JOIN program_accreditation a ON a.program_id = p.id
ORDER BY p.name ASC
  ");
  $rows = $stmt->fetchAll();

  // If empty a.level, set defaults for display only
  foreach($rows as &$r){
    if (!$r['level']) { $r['level'] = 'Level 1'; }
    if (!$r['phase']) { $r['phase'] = 'Phase 1'; }
    if (!$r['status']) { $r['status'] = 'active'; }
  }
  jexit(true, $rows);
}

if($method==='POST'){
  $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
  $pid   = (int)($body['program_id'] ?? 0);
  $level = trim($body['level'] ?? '');
  $phase = trim($body['phase'] ?? '');
  $status= trim($body['status'] ?? 'active');

  if($pid<=0) jexit(false,null,'Missing program_id.');

  $stmt = $pdo->prepare("INSERT INTO program_accreditation(program_id, phase, status)
                         VALUES(:pid, NULLIF(:ph,''), COALESCE(NULLIF(:st,''),'active'))
                         ON DUPLICATE KEY UPDATE phase=VALUES(phase), status=VALUES(status)");
  $stmt->execute([':pid'=>$pid, ':ph'=>$phase, ':st'=>$status]);
  jexit(true, ['saved'=>true]);
}

http_response_code(405);
jexit(false,null,'Method not allowed');
