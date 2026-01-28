<?php
// PHP/indicator_labels_api.php — CRUD for indicators attached to a parameter label
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

  // Ensure prerequisite tables exist (dev convenience)
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS parameter_labels (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      parameter_id INT UNSIGNED NOT NULL,
      name VARCHAR(255) NOT NULL,
      description TEXT NULL,
      sort_order INT UNSIGNED NOT NULL DEFAULT 0,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX(parameter_id), INDEX(sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ");

  $pdo->exec("
    CREATE TABLE IF NOT EXISTS indicator_labels (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      parameter_label_id INT UNSIGNED NOT NULL,
      code VARCHAR(50) NULL,
      title TEXT NOT NULL,
      evidence TEXT NULL,
      sort_order INT UNSIGNED NOT NULL DEFAULT 0,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_ind_pl FOREIGN KEY(parameter_label_id) REFERENCES parameter_labels(id) ON DELETE CASCADE,
      INDEX(parameter_label_id), INDEX(sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ");

  if ($method === 'GET') {
    $lid = intval($_GET['parameter_label_id'] ?? 0);
    if ($lid <= 0) jexit(false, null, 'Missing parameter_label_id', 400);
    $stmt = $pdo->prepare('SELECT id, parameter_label_id, code, title, evidence, sort_order, created_at FROM indicator_labels WHERE parameter_label_id = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$lid]);
    jexit(true, $stmt->fetchAll());
  }

  if ($method === 'POST') {
    $lid  = intval($payload['parameter_label_id'] ?? 0);
    $code = trim($payload['code'] ?? '');
    $title = trim($payload['title'] ?? '');
    $evi   = trim($payload['evidence'] ?? '');
    if ($lid <= 0 || $title === '') jexit(false, null, 'parameter_label_id and title are required', 400);

    $q = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM indicator_labels WHERE parameter_label_id = ?');
    $q->execute([$lid]);
    $next = intval($q->fetchColumn()) + 1;

    $ins = $pdo->prepare('INSERT INTO indicator_labels(parameter_label_id, code, title, evidence, sort_order) VALUES(?, ?, ?, ?, ?)');
    $ins->execute([$lid, $code !== '' ? $code : null, $title, $evi !== '' ? $evi : null, $next]);

    $id = intval($pdo->lastInsertId());
    $row = $pdo->prepare('SELECT id, parameter_label_id, code, title, evidence, sort_order, created_at FROM indicator_labels WHERE id = ?');
    $row->execute([$id]);
    jexit(true, $row->fetch(), null, 201);
  }

  if ($method === 'PUT' || $method === 'PATCH') {
    $id = intval($payload['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $code = array_key_exists('code',$payload) ? trim((string)$payload['code']) : null;
    $title = array_key_exists('title',$payload) ? trim((string)$payload['title']) : null;
    $evi   = array_key_exists('evidence',$payload) ? trim((string)$payload['evidence']) : null;
    $sort  = array_key_exists('sort_order',$payload) ? intval($payload['sort_order']) : null;

    $row = $pdo->prepare('SELECT * FROM indicator_labels WHERE id = ?');
    $row->execute([$id]);
    $cur = $row->fetch();
    if (!$cur) jexit(false, null, 'Not found', 404);

    $code = $code !== null ? ($code !== '' ? $code : null) : $cur['code'];
    $title= $title !== null ? ($title !== '' ? $title : $cur['title']) : $cur['title'];
    $evi  = $evi !== null ? ($evi !== '' ? $evi : null) : $cur['evidence'];
    if ($sort === null) { $sort = $cur['sort_order']; }

    $upd = $pdo->prepare('UPDATE indicator_labels SET code=?, title=?, evidence=?, sort_order=? WHERE id=?');
    $upd->execute([$code, $title, $evi, $sort, $id]);
    jexit(true, ['id'=>$id, 'updated'=>true]);
  }

  if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? ($payload['id'] ?? 0));
    if ($id <= 0) jexit(false, null, 'id is required', 400);
    $pdo->prepare('DELETE FROM indicator_labels WHERE id = ?')->execute([$id]);
    jexit(true, ['id'=>$id]);
  }

  jexit(false, null, 'Method not allowed', 405);
} catch (Throwable $e) {
  jexit(false, null, $e->getMessage(), 500);
}
