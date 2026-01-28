<?php
// section_tree_api.php — Return the full nested tree for a given level_id
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data=null, $err=null, $code=200){
  http_response_code($code);
  echo json_encode(['ok'=>$ok, 'data'=>$data, 'error'=>$err]);
  exit;
}

try {
  $level_id = intval($_GET['level_id'] ?? 0);
  if ($level_id <= 0) jexit(false, null, 'Missing level_id', 400);

  // Sections
  $secStmt = $pdo->prepare("SELECT id, name, description FROM sections WHERE level_id = ? ORDER BY id ASC");
  $secStmt->execute([$level_id]);
  $sections = $secStmt->fetchAll(PDO::FETCH_ASSOC);

  if (!$sections) jexit(true, ['sections'=>[]]); // empty

  // Parameters by section
  $paramStmt = $pdo->prepare("SELECT id, section_id, name, description, sort_order FROM parameters WHERE section_id = ? ORDER BY sort_order ASC, id ASC");
  // Labels by parameter
  $labelStmt = $pdo->prepare("SELECT id, parameter_id, name, description, sort_order FROM parameter_labels WHERE parameter_id = ? ORDER BY sort_order ASC, id ASC");
  // Indicators by label
  $indStmt   = $pdo->prepare("SELECT id, parameter_label_id, code, title, evidence, sort_order FROM indicator_labels WHERE parameter_label_id = ? ORDER BY sort_order ASC, id ASC");

  foreach ($sections as &$s) {
    $paramStmt->execute([$s['id']]);
    $params = $paramStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($params as &$p) {
      $labelStmt->execute([$p['id']]);
      $labels = $labelStmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($labels as &$l) {
        $indStmt->execute([$l['id']]);
        $l['indicators'] = $indStmt->fetchAll(PDO::FETCH_ASSOC);
      }
      $p['labels'] = $labels;
    }
    $s['parameters'] = $params;
  }

  jexit(true, ['sections'=>$sections]);
} catch (Throwable $e) {
  jexit(false, null, $e->getMessage(), 500);
}
