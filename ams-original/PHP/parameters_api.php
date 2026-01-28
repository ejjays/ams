<?php
// PHP/parameters_api.php — CRUD for parameters attached to a section
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $error = null, $code = 200) {
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error]);
  exit;
}

// Create table if missing (move to MIGRATIONS in production)
$pdo->exec("
  CREATE TABLE IF NOT EXISTS parameters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_parameters_section FOREIGN KEY(section_id) REFERENCES sections(id) ON DELETE CASCADE
  )
");

$method = $_SERVER['REQUEST_METHOD'];
$payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

try {
  if ($method === 'GET') {
    if (isset($_GET['id'])) {
      $stmt = $pdo->prepare('SELECT * FROM parameters WHERE id = ?');
      $stmt->execute([ intval($_GET['id']) ]);
      jexit(true, $stmt->fetch());
    }
    $sid = intval($_GET['section_id'] ?? 0);
    if ($sid <= 0) jexit(false, null, 'Missing section_id', 400);
    $stmt = $pdo->prepare('SELECT id, section_id, name, description, sort_order, created_at, updated_at FROM parameters WHERE section_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$sid]);
    jexit(true, $stmt->fetchAll());
  }

  if ($method === 'POST') {
    $sid = intval($payload['section_id'] ?? 0);
    $name = trim($payload['name'] ?? '');
    $desc = trim($payload['description'] ?? '');
    if ($sid <= 0 || $name === '') jexit(false, null, 'section_id and name are required', 400);

    // next sort order within this section
    $q = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM parameters WHERE section_id = ?');
    $q->execute([$sid]);
    $next = intval($q->fetchColumn()) + 1;

    $ins = $pdo->prepare('INSERT INTO parameters(section_id, name, description, sort_order) VALUES(?, ?, ?, ?)');
    $ins->execute([$sid, $name, $desc !== '' ? $desc : null, $next]);

    $id = intval($pdo->lastInsertId());
    $row = $pdo->prepare('SELECT * FROM parameters WHERE id = ?');
    $row->execute([$id]);
    jexit(true, $row->fetch(), null, 201);
  }

  if ($method === 'PUT' || $method === 'PATCH') {
    $id = intval($payload['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $fields = [];
    $vals   = [];
    if (array_key_exists('name', $payload))        { $fields[] = 'name = ?';        $vals[] = trim($payload['name']); }
    if (array_key_exists('description', $payload)) { $fields[] = 'description = ?'; $vals[] = trim($payload['description']); }
    if (array_key_exists('sort_order', $payload))  { $fields[] = 'sort_order = ?';  $vals[] = intval($payload['sort_order']); }
    if (!$fields) jexit(false, null, 'No fields to update', 400);
    $vals[] = $id;
    $sql = 'UPDATE parameters SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $pdo->prepare($sql)->execute($vals);
    $row = $pdo->prepare('SELECT * FROM parameters WHERE id = ?');
    $row->execute([$id]);
    jexit(true, $row->fetch());
  }

  if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? ($payload['id'] ?? 0));
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $pdo->prepare('DELETE FROM parameters WHERE id = ?')->execute([$id]);
    jexit(true, ['id'=>$id]);
  }

  jexit(false, null, 'Method not allowed', 405);

} catch (Throwable $e) {
  jexit(false, null, $e->getMessage(), 500);
}
