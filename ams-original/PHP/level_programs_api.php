<?php
// level_programs_api.php — manage many-to-many link between levels and programs
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php'; // $pdo
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function jexit($ok, $data = null, $err = null) {
  echo json_encode(['ok'=>$ok, 'data'=>$data, 'error'=>$err]);
  exit;
}

try {
  $method = $_SERVER['REQUEST_METHOD'];

  // Ensure tables exist (keep app self-contained; consider moving to migrations)
  $pdo->exec("CREATE TABLE IF NOT EXISTS level_programs (
    level_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (level_id, program_id),
    INDEX idx_lp_level (level_id),
    INDEX idx_lp_program (program_id),
    CONSTRAINT fk_lp_level FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE,
    CONSTRAINT fk_lp_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  if ($method === 'GET') {
    $instrument_id = isset($_GET['instrument_id']) ? (int)$_GET['instrument_id'] : 0;
    $level_id = isset($_GET['level_id']) ? (int)$_GET['level_id'] : 0;
    $program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;

    if ($instrument_id > 0) {
      $stmt = $pdo->prepare("
        SELECT lp.level_id, p.id AS program_id, p.code, p.name
        FROM level_programs lp
        JOIN levels l ON l.id = lp.level_id
        JOIN programs p ON p.id = lp.program_id
        WHERE l.instrument_id = :iid
        ORDER BY lp.level_id, p.name
      ");
      $stmt->execute([':iid'=>$instrument_id]);
      jexit(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($level_id > 0) {
      $stmt = $pdo->prepare("
        SELECT p.id AS program_id, p.code, p.name
        FROM level_programs lp
        JOIN programs p ON p.id = lp.program_id
        WHERE lp.level_id = :lid
        ORDER BY p.name
      ");
      $stmt->execute([':lid'=>$level_id]);
      jexit(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($program_id > 0) {
      $stmt = $pdo->prepare("
        SELECT l.id AS level_id, l.name
        FROM level_programs lp
        JOIN levels l ON l.id = lp.level_id
        WHERE lp.program_id = :pid
        ORDER BY l.name
      ");
      $stmt->execute([':pid'=>$program_id]);
      jexit(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    jexit(true, []);
  }

  if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $level_id = (int)($input['level_id'] ?? 0);
    $program_id = (int)($input['program_id'] ?? 0);
    if ($level_id <= 0 || $program_id <= 0) jexit(false, null, 'Missing level_id or program_id');

    $stmt = $pdo->prepare("INSERT IGNORE INTO level_programs (level_id, program_id) VALUES (:lid, :pid)");
    $stmt->execute([':lid'=>$level_id, ':pid'=>$program_id]);
    jexit(true, ['attached'=>true]);
  }

  if ($method === 'DELETE') {
    $level_id = isset($_GET['level_id']) ? (int)$_GET['level_id'] : 0;
    $program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
    if ($level_id <= 0 || $program_id <= 0) jexit(false, null, 'Missing level_id or program_id');
    $stmt = $pdo->prepare("DELETE FROM level_programs WHERE level_id=:lid AND program_id=:pid");
    $stmt->execute([':lid'=>$level_id, ':pid'=>$program_id]);
    jexit(true, ['detached'=>true]);
  }

  http_response_code(405);
  jexit(false, null, 'Method not allowed');
} catch (Throwable $e) {
  http_response_code(500);
  jexit(false, null, 'Server error');
}
