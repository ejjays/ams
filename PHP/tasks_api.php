<?php
// tasks_api.php — List (and optionally create) tasks, filtered by program
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php'; // gives $pdo

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function jexit($ok, $data=null, $error=null) {
  echo json_encode(['ok'=>$ok,'data'=>$data,'error'=>$error]);
  exit;
}

try {
  $method = $_SERVER['REQUEST_METHOD'];

  // Ensure table exists (safe on repeated calls)
  $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
      id INT AUTO_INCREMENT PRIMARY KEY,
      program_id INT NOT NULL,
      title VARCHAR(255) NOT NULL,
      description TEXT NULL,
      status VARCHAR(50) DEFAULT 'Draft',
      due_date DATE NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT NULL,
      INDEX idx_program (program_id),
      INDEX idx_due (due_date)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  if ($method === 'GET') {
    $pid = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
    $limit = isset($_GET['limit']) ? max(1, min(1000, (int)$_GET['limit'])) : 500;

    if ($pid > 0) {
      $stmt = $pdo->prepare("
        SELECT id, program_id, title, description, status, due_date, created_at, updated_at
        FROM tasks
        WHERE program_id = :pid
        ORDER BY created_at DESC
        LIMIT :lim
      ");
      $stmt->bindValue(':pid', $pid, PDO::PARAM_INT);
      $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
      $stmt->execute();
    } else {
      $stmt = $pdo->prepare("
        SELECT id, program_id, title, description, status, due_date, created_at, updated_at
        FROM tasks
        ORDER BY created_at DESC
        LIMIT :lim
      ");
      $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
      $stmt->execute();
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jexit(true, $rows);
  }

  if ($method === 'POST') {
    // Simple creator to help you seed data from the UI (optional)
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $pid   = isset($input['program_id']) ? (int)$input['program_id'] : 0;
    $title = trim((string)($input['title'] ?? ''));
    $desc  = trim((string)($input['description'] ?? ''));
    $status= trim((string)($input['status'] ?? 'Draft'));
    $due   = trim((string)($input['due_date'] ?? ''));

    if ($pid <= 0 || $title === '') jexit(false, null, 'program_id and title are required.');

    $stmt = $pdo->prepare("
      INSERT INTO tasks (program_id, title, description, status, due_date)
      VALUES (:pid, :title, :desc, :status, :due_date)
    ");
    $stmt->execute([
      ':pid' => $pid,
      ':title' => $title,
      ':desc' => $desc ?: null,
      ':status' => $status ?: 'Draft',
      ':due_date' => $due ?: null,
    ]);

    $id = (int)$pdo->lastInsertId();
    jexit(true, ['id'=>$id]);
  }

  http_response_code(405);
  jexit(false, null, 'Method not allowed');
} catch (Throwable $e) {
  http_response_code(500);
  jexit(false, null, $e->getMessage());
}
