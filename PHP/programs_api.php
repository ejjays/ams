<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

// Error Handling
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
function jexit($ok, $data = null, $error = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error]);
    exit;
}

// Helper function to log actions
function logAudit($pdo, $action, $desc)
{
    $uid = $_SESSION['user_id'] ?? 0;
    // Ensure audit_logs table exists before inserting to avoid fatal errors if SQL wasn't run
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, module, description, ip_address) VALUES (:u, :a, 'PROGRAMS', :d, :ip)");
        $stmt->execute([':u' => $uid, ':a' => $action, ':d' => $desc, ':ip' => $_SERVER['REMOTE_ADDR']]);
    } catch (Throwable $e) {
        // Silently fail logging if table missing, but allow action to proceed
    }
}

$method = $_SERVER['REQUEST_METHOD'];

// -----------------------------------------------------------
// GET: List Active Programs
// -----------------------------------------------------------
if ($method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $sql = "SELECT id, code, name, description, created_at FROM programs 
                WHERE is_archived = 0 
                AND (code LIKE :q1 OR name LIKE :q2)
                ORDER BY name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q1' => "%$q%", ':q2' => "%$q%"]);
    } else {
        $sql = "SELECT id, code, name, description, created_at FROM programs 
                WHERE is_archived = 0 
                ORDER BY name";
        $stmt = $pdo->query($sql);
    }
    jexit(true, $stmt->fetchAll());
}

// -----------------------------------------------------------
// POST: Create (or Smart Restore)
// -----------------------------------------------------------
if ($method === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($code === '' || $name === '') jexit(false, null, 'Code and name required.');

    try {
        $stmt = $pdo->prepare("INSERT INTO programs(code, name, description) VALUES(:c, :n, :d)");
        $stmt->execute([':c' => $code, ':n' => $name, ':d' => $desc]);

        logAudit($pdo, 'CREATE', "Created program $code");
        jexit(true, ['id' => $pdo->lastInsertId(), 'message' => 'Program created.']);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) == 1062) {
            // Check for archived duplicate
            $check = $pdo->prepare("SELECT id, is_archived FROM programs WHERE code = :c");
            $check->execute([':c' => $code]);
            $existing = $check->fetch();

            if ($existing && $existing['is_archived'] == 1) {
                // RESTORE LOGIC
                $restore = $pdo->prepare("UPDATE programs SET is_archived = 0, name = :n, description = :d WHERE id = :id");
                $restore->execute([':n' => $name, ':d' => $desc, ':id' => $existing['id']]);

                logAudit($pdo, 'RESTORE', "Restored program $code from archive");
                jexit(true, ['id' => $existing['id'], 'message' => 'Program restored from archive.']);
            }
            jexit(false, null, 'Program code already exists.');
        }
        jexit(false, null, 'DB Error: ' . $e->getMessage());
    }
}

// -----------------------------------------------------------
// PUT: Update / Edit (ITO ANG NAWALA KANINA)
// -----------------------------------------------------------
if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $id   = (int)($body['id'] ?? 0);
    $code = trim($body['code'] ?? '');
    $name = trim($body['name'] ?? '');
    $desc = trim($body['description'] ?? '');

    if ($id <= 0 || $code === '' || $name === '') {
        jexit(false, null, 'Invalid data for update.');
    }

    try {
        // Get old code for logging purposes
        $oldStmt = $pdo->prepare("SELECT code FROM programs WHERE id = ?");
        $oldStmt->execute([$id]);
        $oldCode = $oldStmt->fetchColumn() ?: 'Unknown';

        $stmt = $pdo->prepare("UPDATE programs SET code=:c, name=:n, description=:d WHERE id=:id");
        $stmt->execute([':c' => $code, ':n' => $name, ':d' => $desc, ':id' => $id]);

        logAudit($pdo, 'UPDATE', "Updated program details for $oldCode -> $code");
        jexit(true, ['updated' => true]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) == 1062) {
            jexit(false, null, 'Program code already exists.');
        }
        jexit(false, null, 'DB error: ' . $e->getMessage());
    }
}

// -----------------------------------------------------------
// DELETE: Archive
// -----------------------------------------------------------
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) jexit(false, null, 'Missing ID.');

    // Fetch name for logging
    $p = $pdo->prepare("SELECT code FROM programs WHERE id = ?");
    $p->execute([$id]);
    $progCode = $p->fetchColumn() ?: 'Unknown';

    $stmt = $pdo->prepare("UPDATE programs SET is_archived = 1 WHERE id=:id");
    $stmt->execute([':id' => $id]);

    logAudit($pdo, 'ARCHIVE', "Archived program $progCode");
    jexit(true, ['deleted' => true, 'message' => 'Program archived.']);
}

http_response_code(405);
jexit(false, null, 'Method not allowed');
