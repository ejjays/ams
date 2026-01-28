<?php
// PHP/parameter_labels_api.php — CRUD for labels attached to a parameter
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $error = null, $code = 200) {
  http_response_code($code);
  echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error]);
  exit;
}

try {
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  $payload = json_decode(file_get_contents('php://input') ?: 'null', true);
  if (!is_array($payload)) { $payload = []; }

  // --- Ensure base tables exist (dev convenience; move to MIGRATIONS in prod) ---
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS parameter_labels (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      parameter_id INT UNSIGNED NOT NULL,
      name VARCHAR(255) NOT NULL,
      sort_order INT UNSIGNED NOT NULL DEFAULT 0,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_param_labels_param FOREIGN KEY (parameter_id) REFERENCES parameters(id) ON DELETE CASCADE,
      INDEX (parameter_id),
      INDEX (sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ");

  // Self-heal: add 'description' column if it does not exist (handles old table versions)
  $descExists = false;
  $colStmt = $pdo->query("SHOW COLUMNS FROM parameter_labels LIKE 'description'");
  if ($colStmt && $colStmt->fetch()) { $descExists = true; }
  if (!$descExists) {
    $pdo->exec("ALTER TABLE parameter_labels ADD COLUMN description TEXT NULL AFTER name");
  }

  if ($method === 'GET') {
    $pid = intval($_GET['parameter_id'] ?? 0);
    if ($pid <= 0) jexit(false, null, 'Missing parameter_id', 400);
    $stmt = $pdo->prepare('SELECT id, parameter_id, name, description, sort_order, created_at FROM parameter_labels WHERE parameter_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$pid]);
    jexit(true, $stmt->fetchAll());
  }

  if ($method === 'POST') {
    $pid = intval($payload['parameter_id'] ?? 0);
    $name = trim($payload['name'] ?? '');
    $desc = trim($payload['description'] ?? '');
    if ($pid <= 0 || $name === '') jexit(false, null, 'parameter_id and name are required', 400);

    // next sort order within this parameter
    $q = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM parameter_labels WHERE parameter_id = ?');
    $q->execute([$pid]);
    $next = intval($q->fetchColumn()) + 1;

    $ins = $pdo->prepare('INSERT INTO parameter_labels(parameter_id, name, description, sort_order) VALUES(?, ?, ?, ?)');
    $ins->execute([$pid, $name, $desc !== '' ? $desc : null, $next]);

    $id = intval($pdo->lastInsertId());
    $row = $pdo->prepare('SELECT id, parameter_id, name, description, sort_order, created_at FROM parameter_labels WHERE id = ?');
    $row->execute([$id]);
    jexit(true, $row->fetch(), null, 201);
  }

  if ($method === 'PUT' || $method === 'PATCH') {
    $id = intval($payload['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $name = trim($payload['name'] ?? '');
    $desc = trim($payload['description'] ?? '');
    $sort = isset($payload['sort_order']) ? intval($payload['sort_order']) : null;

    $row = $pdo->prepare('SELECT * FROM parameter_labels WHERE id = ?');
    $row->execute([$id]);
    $cur = $row->fetch();
    if (!$cur) jexit(false, null, 'Not found', 404);

    $name = $name !== '' ? $name : $cur['name'];
    $desc = $desc !== '' ? $desc : $cur['description'];
    if ($sort === null) { $sort = $cur['sort_order']; }

    $upd = $pdo->prepare('UPDATE parameter_labels SET name=?, description=?, sort_order=? WHERE id=?');
    $upd->execute([$name, $desc, $sort, $id]);
    jexit(true, ['id'=>$id, 'updated'=>true]);
  }

  if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? ($payload['id'] ?? 0));
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $pdo->prepare('DELETE FROM parameter_labels WHERE id = ?')->execute([$id]);
    jexit(true, ['id'=>$id]);
  }

  jexit(false, null, 'Method not allowed', 405);
} catch (Throwable $e) {
  jexit(false, null, $e->getMessage(), 500);
}
