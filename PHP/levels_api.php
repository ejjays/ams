<?php
// levels_api.php — CRUD for Levels (scoped by instrument_id)
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php'; // provides $pdo
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function jexit($ok, $data = null, $err = null)
{
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
  exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

// Ensure table exists (keep app self-contained; consider moving to MIGRATIONS in prod)
$pdo->exec("CREATE TABLE IF NOT EXISTS levels (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrument_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description VARCHAR(255) NULL,
  weight INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_levels_instrument (instrument_id),
  CONSTRAINT fk_levels_instrument FOREIGN KEY (instrument_id) REFERENCES instruments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
  if ($method === 'GET') {
    $inst = isset($_GET['instrument_id']) ? (int)$_GET['instrument_id'] : 0;
    if ($inst <= 0) jexit(false, null, 'Missing instrument_id');
    $stmt = $pdo->prepare('SELECT id, name, description, weight FROM levels WHERE instrument_id = :i ORDER BY weight DESC, id ASC');
    $stmt->execute([':i' => $inst]);
    $items = $stmt->fetchAll();
    jexit(true, ['items' => $items]);
  }

  if ($method === 'POST') {
    $id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $inst = isset($_POST['instrument_id']) ? (int)$_POST['instrument_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $w    = isset($_POST['weight']) ? (int)$_POST['weight'] : 0;

    if ($inst <= 0 || $name === '') jexit(false, null, 'instrument_id and name are required');
    if (mb_strlen($name) > 255) jexit(false, null, 'Name too long');
    if (mb_strlen($desc) > 255) jexit(false, null, 'Description too long');

    if ($id > 0) {
      $stmt = $pdo->prepare('UPDATE levels SET name=:n, description=:d, weight=:w WHERE id=:id AND instrument_id=:i');
      $stmt->execute([':n' => $name, ':d' => $desc, ':w' => $w, ':id' => $id, ':i' => $inst]);
      jexit(true, ['id' => $id, 'updated' => true]);
    } else {
      $stmt = $pdo->prepare('INSERT INTO levels (instrument_id, name, description, weight) VALUES (:i, :n, :d, :w)');
      $stmt->execute([':i' => $inst, ':n' => $name, ':d' => $desc, ':w' => $w]);
      jexit(true, ['id' => (int)$pdo->lastInsertId(), 'created' => true]);
    }
  }

  if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) jexit(false, null, 'Invalid id');
    $stmt = $pdo->prepare('DELETE FROM levels WHERE id=:id');
    $stmt->execute([':id' => $id]);
    jexit(true, ['deleted' => true]);
  }

  http_response_code(405);
  jexit(false, null, 'Method not allowed');
} catch (Throwable $e) {
  http_response_code(500);
  jexit(false, null, 'Server error');
}
