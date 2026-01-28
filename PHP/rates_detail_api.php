<?php
// rates_detail_api.php — detail data for Ratings modal
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok,$data=null,$err=null){ echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$err]); exit; }

try {
  $pid = (int)($_GET['program_id'] ?? 0);
  if ($pid <= 0) { http_response_code(400); jexit(false,null,'Missing program_id'); }

  // Resolve the selected level_id for this program: highest weight, then newest id
  $stmt = $pdo->prepare("
    SELECT l.id AS level_id, l.name AS level_name
    FROM level_programs lp
    JOIN levels l ON l.id = lp.level_id
    WHERE lp.program_id = :pid
    ORDER BY l.weight DESC, l.id DESC
    LIMIT 1
  ");
  $stmt->execute([':pid'=>$pid]);
  $level = $stmt->fetch(PDO::FETCH_ASSOC);

  // Fallback to synthetic Level 1 if none linked
  $level_id = $level['level_id'] ?? null;
  $level_name = $level['level_name'] ?? 'Level 1';

  // Phase and program meta from program_accreditation
  $stmt = $pdo->prepare("
    SELECT p.code, p.name, COALESCE(a.phase,'Phase 1') AS phase
    FROM programs p
    LEFT JOIN program_accreditation a ON a.program_id = p.id
    WHERE p.id = :pid
  ");
  $stmt->execute([':pid'=>$pid]);
  $meta = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$meta) { jexit(false, null, 'Program not found'); }

  // Areas (Sections) for this level
  if ($level_id) {
    $stmt = $pdo->prepare("SELECT id, name FROM sections WHERE level_id=:lid ORDER BY id ASC");
    $stmt->execute([':lid'=>$level_id]);
  } else {
    // No level → no sections associated; return empty list
    $stmt = false;
  }
  $areas = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

  // Total score placeholder (no rubric yet)
  $total_score = 0.0;

  jexit(true, [
    'program_id' => $pid,
    'program_code' => $meta['code'],
    'program_name' => $meta['name'],
    'level' => $level_name,
    'phase' => $meta['phase'],
    'total_score' => $total_score,
    'areas' => array_map(function($r){ return ['id'=>(int)$r['id'], 'name'=>$r['name']]; }, $areas),
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  jexit(false, null, 'Server error');
}
