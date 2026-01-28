<?php
// PHP/coordinators_api.php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code = 400)
{
  http_response_code($code);
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}
function ok($data = null)
{
  echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS program_coordinators (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_prog_user (program_id,user_id),
    KEY idx_prog (program_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

  $method = $_SERVER['REQUEST_METHOD'];
  if ($method === 'GET') {
    $program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
    if ($program_id <= 0) fail('Missing program_id');

    // Current coordinators for program
    $stmt = $pdo->prepare("SELECT pc.user_id, u.first_name, u.last_name, u.email 
                           FROM program_coordinators pc 
                           JOIN users u ON u.id=pc.user_id
                           WHERE pc.program_id=?");
    $stmt->execute([$program_id]);
    $current = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Eligible users (role = program_coordinator)
    $eligible = $pdo->query("SELECT id AS user_id, first_name, last_name, email 
                              FROM users WHERE role='program_coordinator' 
                              ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);

    ok(['current' => $current, 'eligible' => $eligible]);
  }

  // Assign
  if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $program_id = isset($input['program_id']) ? (int)$input['program_id'] : 0;
    $user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    if ($program_id <= 0 || $user_id <= 0) fail('Missing program_id or user_id');

    $stmt = $pdo->prepare("INSERT IGNORE INTO program_coordinators (program_id,user_id) VALUES (?,?)");
    $stmt->execute([$program_id, $user_id]);
    ok(['assigned' => true]);
  }

  // Unassign
  if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $input);
    $program_id = isset($input['program_id']) ? (int)$input['program_id'] : 0;
    $user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    if ($program_id <= 0 || $user_id <= 0) fail('Missing program_id or user_id');
    $stmt = $pdo->prepare("DELETE FROM program_coordinators WHERE program_id=? AND user_id=?");
    $stmt->execute([$program_id, $user_id]);
    ok(['removed' => true]);
  }

  http_response_code(405);
  fail('Method not allowed', 405);
} catch (Throwable $e) {
  http_response_code(500);
  fail($e->getMessage(), 500);
}
