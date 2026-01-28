<?php
// instruments_api.php — CRUD for instruments
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php'; // <-- ITO ANG MAHALAGANG PAGBABAGO. Ginagamit na nito ang tamang $pdo.

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function jexit($ok, $data = null, $err = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

/*
 * TINANGGAL ANG MALI/DUPLICATE NA DATABASE CONNECTION DITO.
 * Ang $pdo variable ay galing na sa /db.php
 */

// Ensure table (idempotent)
$pdo->exec("
  CREATE TABLE IF NOT EXISTS instruments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $stmt = $pdo->prepare("SELECT id, name, description FROM instruments WHERE name LIKE :q ORDER BY id ASC");
        $stmt->execute([':q' => "%{$q}%"]);
    } else {

        $stmt = $pdo->query("SELECT id, name, description FROM instruments ORDER BY id ASC");
    }
    jexit(true, ['items' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name === '') jexit(false, null, 'Instrument name is required.');

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE instruments SET name=:n, description=:d WHERE id=:id");
        $stmt->execute([':n' => $name, ':d' => $desc, ':id' => $id]);
        jexit(true, ['id' => $id, 'updated' => true]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO instruments(name, description, created_by) VALUES (:n,:d,:u)");
        $stmt->execute([':n' => $name, ':d' => $desc, ':u' => $userId]);
        $newId = (int)$pdo->lastInsertId();
        jexit(true, ['id' => $newId, 'created' => true]);
    }
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) jexit(false, null, 'Invalid id');
    $stmt = $pdo->prepare("DELETE FROM instruments WHERE id=:id");
    $stmt->execute([':id' => $id]);
    jexit(true, ['deleted' => true]);
}

http_response_code(405);
jexit(false, null, 'Method not allowed');
