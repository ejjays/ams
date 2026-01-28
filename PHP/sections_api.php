<?php
// sections_api.php — CRUD for Sections (scoped by level_id)
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data=null, $err=null){ echo json_encode(['ok'=>$ok, 'data'=>$data, 'error'=>$err]); exit; }

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

// Table (consider moving DDL to MIGRATIONS in prod)
$pdo->exec("CREATE TABLE IF NOT EXISTS sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_sections_level (level_id),
  CONSTRAINT fk_sections_level FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if ($method === 'GET') {
    $lvl = (int)($_GET['level_id'] ?? 0);
    if ($lvl <= 0) jexit(false, null, 'Missing level_id');
    $stmt = $pdo->prepare('SELECT id, name, description FROM sections WHERE level_id = :l ORDER BY id DESC');
    $stmt->execute([':l'=>$lvl]);
    jexit(true, ['items'=>$stmt->fetchAll()]);
  }

  if ($method === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $lvl  = (int)($_POST['level_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($lvl <= 0 || $name === '') jexit(false, null, 'level_id and name are required');
    if (mb_strlen($name) > 255) jexit(false, null, 'Name too long');
    if (mb_strlen($desc) > 255) jexit(false, null, 'Description too long');

    if ($id > 0) {
      $stmt = $pdo->prepare('UPDATE sections SET name=:n, description=:d WHERE id=:id AND level_id=:l');
      $stmt->execute([':n'=>$name, ':d'=>$desc, ':id'=>$id, ':l'=>$lvl]);
      jexit(true, ['id'=>$id, 'updated'=>true]);
    } else {
      $stmt = $pdo->prepare('INSERT INTO sections (level_id, name, description) VALUES (:l, :n, :d)');
      $stmt->execute([':l'=>$lvl, ':n'=>$name, ':d'=>$desc]);
      jexit(true, ['id'=>(int)$pdo->lastInsertId(), 'created'=>true]);
    }
  }

  if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Invalid id');
    $stmt = $pdo->prepare('DELETE FROM sections WHERE id=:id');
    $stmt->execute([':id'=>$id]);
    jexit(true, ['deleted'=>true]);
  }

  http_response_code(405);
  jexit(false, null, 'Method not allowed');
} catch (Throwable $e) {
  http_response_code(500);
  jexit(false, null, 'Server error');
}
