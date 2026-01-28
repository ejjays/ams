<?php
require __DIR__ . '/auth_guard.php'; // session
require __DIR__ . '/db.php'; // <-- ITO ANG PAGBABAGO. Ginagamit na nito ang tamang $pdo.

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

set_error_handler(function ($no, $str) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => "PHP: $str"]);
  exit;
});
set_exception_handler(function ($e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
  exit;
});
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

// Ensure base documents table (reuse existing schema) and link table
$pdo->exec("
CREATE TABLE IF NOT EXISTS indicator_document_links (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  indicator_id INT UNSIGNED NOT NULL,
  document_id INT UNSIGNED NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_indicator (indicator_id),
  KEY idx_document (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

$docsDir = realpath(__DIR__ . '/../uploads/documents');

// Ensure documents table exists (mirror of documents_api minimal schema)
$pdo->exec("
  CREATE TABLE IF NOT EXISTS documents (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    comment TEXT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_ext VARCHAR(16) NULL,
    mime_type VARCHAR(128) NULL,
    file_size INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_docs_owner (owner_user_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
");

if (!$docsDir) {
  $docsDir = __DIR__ . '/../uploads/documents';
  if (!is_dir($docsDir)) @mkdir($docsDir, 0775, true);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Simple list for a given indicator
if ($method === 'GET' && $action === 'list') {
  $ind = (int)($_GET['indicator_id'] ?? 0);
  if ($ind <= 0) jexit(false, null, 'indicator_id required.');
  $stmt = $pdo->prepare("
    SELECT l.id link_id, d.id doc_id, d.title, d.original_name, d.stored_name, d.file_ext, d.file_size, d.created_at
    FROM indicator_document_links l
    JOIN documents d ON d.id=l.document_id
    WHERE l.indicator_id=:i
    ORDER BY d.created_at DESC, d.id DESC
  ");
  $stmt->execute([':i' => $ind]);
  jexit(true, $stmt->fetchAll());
}

// Upload a file and link it to an indicator
if ($method === 'POST' && $action === 'upload') {
  $ind = (int)($_POST['indicator_id'] ?? 0);
  if ($ind <= 0) jexit(false, null, 'indicator_id required.');

  if (!isset($_FILES['file'])) jexit(false, null, 'file required.');
  $f = $_FILES['file'];
  if ($f['error'] !== UPLOAD_ERR_OK) jexit(false, null, 'Upload error.');
  $orig = basename($f['name']);
  $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
  // Basic extension allowlist (expand as needed)
  $allowed = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
  if ($ext && !in_array($ext, $allowed, true)) jexit(false, null, 'File type not allowed.');

  $stored = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext ?: 'bin');
  $dest   = rtrim($docsDir, '/') . '/' . $stored;
  if (!@move_uploaded_file($f['tmp_name'], $dest)) jexit(false, null, 'Failed to store file.');

  // Insert into documents
  $stmt = $pdo->prepare("
    INSERT INTO documents (owner_user_id,title,comment,original_name,stored_name,file_ext,mime_type,file_size)
    VALUES (:uid,:t,:c,:o,:s,:x,:m,:z)
  ");
  $mime = mime_content_type($dest) ?: ($f['type'] ?: 'application/octet-stream');
  $size = (int)$f['size'];
  $title = $_POST['title'] ?? $orig; // Use original name as title if not provided

  $stmt->execute([
    ':uid' => $userId,
    ':t' => $title,
    ':c' => '',
    ':o' => $orig,
    ':s' => $stored,
    ':x' => $ext,
    ':m' => $mime,
    ':z' => $size
  ]);
  $docId = (int)$pdo->lastInsertId();

  // Link
  $stmt = $pdo->prepare("INSERT INTO indicator_document_links (indicator_id, document_id, uploaded_by) VALUES (:i,:d,:u)");
  $stmt->execute([':i' => $ind, ':d' => $docId, ':u' => $userId]);

  jexit(true, ['document_id' => $docId, 'stored_name' => $stored, 'original_name' => $orig]);
}

jexit(false, null, 'Invalid action.');
