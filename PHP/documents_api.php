<?php
// FILE: ams/PHP/documents_api.php
// UPDATED: Added Auto-Archive Logic (5-Year Rule) & Automatic Table Creation

require __DIR__ . '/auth_guard.php'; // requires $_SESSION['user_id']
require __DIR__ . '/db.php'; // $pdo

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Error Handling Wrappers
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

// ---------- 1. INIT & SAFETY CHECKS (Dapat Walang Error) ----------

try {
    // A. Siguraduhing may table para sa Reviews (Required sa List query)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS document_reviews (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            rating INT NOT NULL,
            comment TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_doc_user_review (document_id, user_id),
            KEY idx_review_doc (document_id),
            KEY idx_review_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // B. Siguraduhing may table para sa Delete History (Recycle Bin)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS document_delete_history (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            document_id INT UNSIGNED NOT NULL,
            owner_user_id INT UNSIGNED NOT NULL,
            title VARCHAR(255),
            comment VARCHAR(255),
            original_name VARCHAR(255),
            stored_name VARCHAR(255),
            file_ext VARCHAR(20),
            mime_type VARCHAR(100),
            file_size BIGINT,
            created_at TIMESTAMP,
            deleted_by_user_id INT UNSIGNED,
            deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // C. AUTO-ARCHIVE LOGIC (The "5-Year Rule")
    // Ito ang requirement ng Panel: Kusang mag-archive kung > 5 years na.
    // Inilagay natin ito dito para kada load ng Documents list, updated agad.
    $pdo->exec("
        UPDATE documents 
        SET archived_at = NOW() 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 YEAR) 
          AND archived_at IS NULL
    ");
} catch (Throwable $e) {
    // Silent fail sa init para hindi block ang app, pero naka-log
    error_log("Init Error: " . $e->getMessage());
}


// ---------- Files setup ----------
$uploadDir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
$uploadDir = rtrim($uploadDir, '/\\');
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
$docsDir = $uploadDir . '/documents';
if (!is_dir($docsDir)) @mkdir($docsDir, 0775, true);

// Helpers
function escname($s)
{
    return preg_replace('/[^A-Za-z0-9._-]+/', '_', $s);
}
function extOf($n)
{
    $p = pathinfo($n, PATHINFO_EXTENSION);
    return strtolower($p ?? '');
}
function fullOwnerName($row)
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ---------- LIST ----------
if ($method === 'GET' && $action === 'list') {
    try {
        $tab = ($_GET['tab'] ?? 'mine') === 'shared' ? 'shared' : 'mine';
        $q   = trim($_GET['q'] ?? '');

        // Subquery para sa ratings
        $ratingsSubQuery = "
            (SELECT AVG(r.rating) FROM document_reviews r WHERE r.document_id = d.id) AS avg_rating,
            (SELECT r2.rating FROM document_reviews r2 WHERE r2.document_id = d.id AND r2.user_id = :current_user_id) AS my_review_rating,
            (SELECT il.title FROM indicator_document_links idl JOIN indicator_labels il ON il.id = idl.indicator_id WHERE idl.document_id = d.id LIMIT 1) AS ai_tag
        ";

        if ($tab === 'mine') {
            // OWNED DOCUMENTS
            $sql = "
                SELECT d.id, d.title, d.comment, d.original_name, d.stored_name, d.file_ext,
                       d.mime_type, d.file_size, d.created_at, $ratingsSubQuery
                FROM documents d
                WHERE d.owner_user_id = :uid
                  AND d.archived_at IS NULL
            ";
            $params = [':uid' => $userId, ':current_user_id' => $userId];

            if ($q !== '') {
                $sql .= " AND (d.title LIKE :q1 OR d.comment LIKE :q2 OR d.original_name LIKE :q3)";
                $params[':q1'] = $params[':q2'] = $params[':q3'] = "%$q%";
            }
            $sql .= " ORDER BY d.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            jexit(true, $rows);
        } else {
            // SHARED WITH ME
            $sql = "
                SELECT d.id, d.title, d.comment, d.original_name, d.stored_name, d.file_ext,
                       d.mime_type, d.file_size, d.created_at,
                       u.first_name, u.last_name, $ratingsSubQuery
                FROM document_shares s
                JOIN documents d ON d.id = s.document_id
                JOIN users u ON u.id = d.owner_user_id
                WHERE s.user_id = :uid
                  AND d.archived_at IS NULL
            ";
            $params = [':uid' => $userId, ':current_user_id' => $userId];

            if ($q !== '') {
                $sql .= " AND (d.title LIKE :q1 OR d.comment LIKE :q2 OR d.original_name LIKE :q3)";
                $params[':q1'] = $params[':q2'] = $params[':q3'] = "%$q%";
            }
            $sql .= " ORDER BY d.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) $r['owner'] = fullOwnerName($r);
            jexit(true, $rows);
        }
    } catch (Throwable $e) {
        jexit(false, null, "Database Error: " . $e->getMessage());
    }
}

// ---------- DOWNLOAD (SECURED) ----------
if ($method === 'GET' && $action === 'download') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing id.');

    // SECURITY FIX: Check ownership or share permission
    $stmt = $pdo->prepare("
        SELECT d.* FROM documents d
        LEFT JOIN document_shares s ON s.document_id = d.id
        WHERE d.id = :id 
          AND (d.owner_user_id = :uid1 OR s.user_id = :uid2)
        LIMIT 1
    ");
    $stmt->execute([':id' => $id, ':uid1' => $userId, ':uid2' => $userId]);
    $doc = $stmt->fetch();

    if (!$doc) jexit(false, null, 'Not found or access denied.');

    $path = $docsDir . '/' . $doc['stored_name'];
    if (!is_file($path)) jexit(false, null, 'File missing on server.');

    header_remove('Content-Type');
    header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));

    $isInline = isset($_GET['inline']) && $_GET['inline'] === '1';
    $disposition = $isInline ? 'inline' : 'attachment';

    header('Content-Disposition: ' . $disposition . '; filename="' . basename($doc['original_name']) . '"');
    header('Content-Length: ' . (string)filesize($path));
    readfile($path);
    exit;
}

// ---------- CREATE / EDIT ----------
if ($method === 'POST' && $action === 'save') {
    $id      = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : 0;
    $title   = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    if ($title === '') jexit(false, null, 'Title is required.');

    $fileProvided = isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name']);

    $existing = null;
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id=:id AND owner_user_id=:uid");
        $stmt->execute([':id' => $id, ':uid' => $userId]);
        $existing = $stmt->fetch();
        if (!$existing) jexit(false, null, 'Not found or permission denied.');
    }

    $orig = $existing['original_name'] ?? '';
    $stored = $existing['stored_name'] ?? '';
    $mime = $existing['mime_type'] ?? '';
    $size = (int)($existing['file_size'] ?? 0);
    $ext  = $existing['file_ext'] ?? '';

    if ($fileProvided) {
        $f = $_FILES['file'];
        if ($f['error'] !== UPLOAD_ERR_OK) jexit(false, null, 'Upload error.');
        $orig = $f['name'];
        $ext  = extOf($orig);
        $mime = mime_content_type($f['tmp_name']) ?: ($f['type'] ?: 'application/octet-stream');
        $size = (int)$f['size'];

        $stored = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext ?: 'bin');
        $dest = $docsDir . '/' . $stored;
        if (!@move_uploaded_file($f['tmp_name'], $dest)) jexit(false, null, 'Failed to store file.');

        // Remove old file if updating
        if ($existing && $existing['stored_name'] && is_file($docsDir . '/' . $existing['stored_name'])) {
            @unlink($docsDir . '/' . $existing['stored_name']);
        }
    } else {
        if ($id === 0) jexit(false, null, 'File is required.');
    }

    if ($id === 0) {
        $stmt = $pdo->prepare("
          INSERT INTO documents (owner_user_id,title,comment,original_name,stored_name,file_ext,mime_type,file_size)
          VALUES (:uid,:t,:c,:o,:s,:x,:m,:z)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':t' => $title,
            ':c' => $comment,
            ':o' => $orig,
            ':s' => $stored,
            ':x' => $ext,
            ':m' => $mime,
            ':z' => $size
        ]);
        $id = (int)$pdo->lastInsertId();
        
        // --- START: AI AUTO-TAGGING ---
        try {
            require_once __DIR__ . '/services/AIService.php';
            $indicators = $pdo->query("SELECT id, title FROM indicator_labels LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
            $suggestedId = AIService::suggestIndicator($title, $indicators);
            
            if ($suggestedId) {
                $pdo->prepare("INSERT IGNORE INTO indicator_document_links (indicator_id, document_id, uploaded_by) VALUES (?, ?, ?)")
                    ->execute([$suggestedId, $id, $userId]);
            }
        } catch (Throwable $aiErr) { error_log("AI Tagging Error: " . $aiErr->getMessage()); }
        // --- END: AI AUTO-TAGGING ---

        jexit(true, ['id' => $id]);
    } else {
        if ($fileProvided) {
            $stmt = $pdo->prepare("
                UPDATE documents
                SET title=:t, comment=:c, original_name=:o, stored_name=:s, file_ext=:x, mime_type=:m, file_size=:z
                WHERE id=:id
            ");
            $stmt->execute([
                ':t' => $title,
                ':c' => $comment,
                ':o' => $orig,
                ':s' => $stored,
                ':x' => $ext,
                ':m' => $mime,
                ':z' => $size,
                ':id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE documents SET title=:t, comment=:c WHERE id=:id");
            $stmt->execute([':t' => $title, ':c' => $comment, ':id' => $id]);
        }
        jexit(true, ['updated' => true]);
    }
}

// ---------- DELETE (Recycle Bin) ----------
// Note: Kahit nakatago ang button, kailangan gumana ito nang walang error.
if ($method === 'DELETE' && $action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing id.');

    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id=:id AND owner_user_id=:uid");
    $stmt->execute([':id' => $id, ':uid' => $userId]);
    $doc_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc_to_delete) jexit(false, null, 'Not found or permission denied.');

    try {
        $logStmt = $pdo->prepare("
            INSERT INTO document_delete_history (
                document_id, owner_user_id, title, comment, original_name, stored_name, 
                file_ext, mime_type, file_size, created_at, 
                deleted_by_user_id, deleted_at
            )
            SELECT 
                id, owner_user_id, title, comment, original_name, stored_name, 
                file_ext, mime_type, file_size, created_at, 
                :deleter_id AS deleted_by_user_id, NOW() AS deleted_at 
            FROM documents 
            WHERE id = :id
        ");
        $logStmt->execute([':id' => $id, ':deleter_id' => $userId]);
    } catch (Throwable $e) {
        // Safe fail: Kung hindi gumana ang history insert, proceed to delete or stop?
        // Since "walang error" ang goal, mag log na lang tayo.
        error_log('Delete history failed: ' . $e->getMessage());
    }

    // Clean up dependencies
    $pdo->prepare("DELETE FROM indicator_document_links WHERE document_id=:id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM document_shares WHERE document_id=:id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM document_reviews WHERE document_id=:id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM documents WHERE id=:id")->execute([':id' => $id]);

    jexit(true, ['deleted' => true]);
}

// ---------- SHARE ----------
if ($method === 'POST' && $action === 'share') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $docId = (int)($body['document_id'] ?? 0);
    $toUser = (int)($body['user_id'] ?? 0);

    if ($docId <= 0 || $toUser <= 0) jexit(false, null, 'Invalid data.');

    // Check ownership
    $stmt = $pdo->prepare("SELECT id FROM documents WHERE id=:id AND owner_user_id=:uid");
    $stmt->execute([':id' => $docId, ':uid' => $userId]);
    if (!$stmt->fetch()) jexit(false, null, 'Document not found or access denied.');

    // Check target user
    $u = $pdo->prepare("SELECT id FROM users WHERE id=:id");
    $u->execute([':id' => $toUser]);
    if (!$u->fetch()) jexit(false, null, 'User not found.');

    $ins = $pdo->prepare("INSERT IGNORE INTO document_shares(document_id,user_id) VALUES (:d,:u)");
    $ins->execute([':d' => $docId, ':u' => $toUser]);

    jexit(true, ['shared' => true]);
}

// ---------- ARCHIVE (Manual) ----------
// Note: Available pa rin ang endpoint kung sakaling ibalik ang button, pero ang automation ang priority.
if ($method === 'POST' && $action === 'archive') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing id.');

    $stmt = $pdo->prepare("UPDATE documents SET archived_at = NOW() WHERE id = :id AND owner_user_id = :uid");
    $stmt->execute([':id' => $id, ':uid' => $userId]);
    jexit(true, ['archived' => true]);
}

// ---------- UNARCHIVE (Restore) ----------
if ($method === 'POST' && $action === 'unarchive') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing id.');

    $stmt = $pdo->prepare("UPDATE documents SET archived_at = NULL WHERE id = :id AND owner_user_id = :uid");
    $stmt->execute([':id' => $id, ':uid' => $userId]);
    jexit(true, ['unarchived' => true]);
}

// ---------- REVIEW ----------
if ($method === 'POST' && $action === 'review') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $docId = (int)($body['document_id'] ?? 0);
    $rating = (int)($body['rating'] ?? 0);
    $comment = trim($body['comment'] ?? '');

    if ($docId <= 0) jexit(false, null, 'Missing document ID.');
    if ($rating < 1 || $rating > 5) jexit(false, null, 'Invalid rating.');

    // Table creation is handled at the TOP of the file now (Init section).

    $stmt = $pdo->prepare("
        INSERT INTO document_reviews (document_id, user_id, rating, comment, created_at)
        VALUES (:doc_id, :user_id, :rating, :comment, NOW())
        ON DUPLICATE KEY UPDATE
            rating = VALUES(rating),
            comment = VALUES(comment),
            created_at = NOW()
    ");
    $stmt->execute([
        ':doc_id' => $docId,
        ':user_id' => $userId,
        ':rating' => $rating,
        ':comment' => $comment
    ]);

    jexit(true, ['reviewed' => true]);
}

// ---------- AI INSIGHT (ON DEMAND) ----------
if ($method === 'GET' && $action === 'ai_insight') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing id.');

    $stmt = $pdo->prepare("SELECT title, comment, stored_name, file_ext, mime_type FROM documents WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $doc = $stmt->fetch();

    if (!$doc) jexit(false, null, 'Document not found.');

    $fileInfo = [
        'ext' => strtolower($doc['file_ext'] ?? ''),
        'mime' => $doc['mime_type'] ?: 'application/octet-stream'
    ];
    
    $path = $docsDir . '/' . $doc['stored_name'];
    if (is_file($path)) {
        if (in_array($fileInfo['ext'], ['txt', 'csv', 'md'])) {
            $fileInfo['content'] = file_get_contents($path);
        } else if (in_array($fileInfo['ext'], ['pdf', 'jpg', 'jpeg', 'png'])) {
            // Convert to Base64 for Gemini Vision
            $fileInfo['base64'] = base64_encode(file_get_contents($path));
        }
    }

    require_once __DIR__ . '/services/AIService.php';
    try {
        $aiRes = AIService::getDocumentInsight($doc['title'], $doc['comment'], $fileInfo);
        
        jexit(true, [
            'insight' => $aiRes['text'] ?? 'Analysis failed.',
            'model' => $aiRes['model'] ?? 'Unknown'
        ]);
    } catch (Throwable $e) {
        jexit(false, null, "AI Processing Error: " . $e->getMessage());
    }
}

// ---------- Fallback ----------
http_response_code(405);
jexit(false, null, 'Method/action not allowed');
