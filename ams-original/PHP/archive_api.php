<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $err = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
    exit;
}

// 1. AUTHENTICATION CHECK
$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

// 2. HANDLE REQUEST METHOD
$method = $_SERVER['REQUEST_METHOD'];

// =========================================================
//  PART A: POST REQUESTS (ACTIONS: RESTORE)
// =========================================================
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $id = (int)($input['id'] ?? 0);
    $type = $input['type'] ?? 'documents';

    if ($action === 'restore' && $id > 0) {
        try {
            $pdo->beginTransaction();

            if ($type === 'documents') {
                // 1. Get details
                $stmt = $pdo->prepare("SELECT title FROM documents WHERE id = ?");
                $stmt->execute([$id]);
                $doc = $stmt->fetch();

                if (!$doc) throw new Exception("Document not found.");

                // 2. Restore Document
                $update = $pdo->prepare("UPDATE documents SET archived_at = NULL WHERE id = ?");
                $update->execute([$id]);

                // 3. Log to Audit Trail
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (user_id, action, module, description, created_at) 
                    VALUES (?, 'RESTORE', 'DOCUMENTS', ?, NOW())
                ");
                $desc = "Restored document: " . $doc['title'] . " from archive";
                $logStmt->execute([$userId, $desc]);
            } elseif ($type === 'programs') {
                // 1. Get details
                $stmt = $pdo->prepare("SELECT name FROM programs WHERE id = ?");
                $stmt->execute([$id]);
                $prog = $stmt->fetch();

                if (!$prog) throw new Exception("Program not found.");

                // 2. Restore Program
                $update = $pdo->prepare("UPDATE programs SET is_archived = 0 WHERE id = ?");
                $update->execute([$id]);

                // 3. Log to Audit Trail
                $logStmt = $pdo->prepare("
                    INSERT INTO audit_logs (user_id, action, module, description, created_at) 
                    VALUES (?, 'RESTORE', 'PROGRAMS', ?, NOW())
                ");
                $desc = "Restored program: " . $prog['name'] . " from archive";
                $logStmt->execute([$userId, $desc]);
            }

            $pdo->commit();
            jexit(true, ['message' => 'Item restored successfully.']);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            jexit(false, null, 'Restore failed: ' . $e->getMessage());
        }
    }
    jexit(false, null, 'Invalid action or ID.');
}

// =========================================================
//  PART B: GET REQUESTS (LISTING)
// =========================================================
$type = $_GET['type'] ?? 'documents';

try {
    /* -----------------------------------------------------------
       TEMPORARILY DISABLED: AUTO-ARCHIVE LOGIC
       Ito ang dahilan kung bakit bumabalik agad sa archive ang files
       kapag ang 'created_at' nila ay NULL o matagal na.
       ----------------------------------------------------------- */
    // $autoArchive = $pdo->prepare("
    //     UPDATE documents 
    //     SET archived_at = NOW() 
    //     WHERE created_at < DATE_SUB(NOW(), INTERVAL 5 YEAR) 
    //     AND archived_at IS NULL
    // ");
    // $autoArchive->execute();

    // 2. FETCH DATA
    if ($type === 'programs') {
        $stmt = $pdo->prepare("
            SELECT id, code, name, description, created_at 
            FROM programs 
            WHERE is_archived = 1 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jexit(true, $items);
    } else {
        $stmt = $pdo->query("
            SELECT 
                d.id, 
                d.title, 
                d.original_name, 
                d.archived_at,
                d.created_at,
                CONCAT(u.first_name, ' ', u.last_name) AS owner_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.owner_user_id
            WHERE d.archived_at IS NOT NULL
            ORDER BY d.archived_at DESC
            LIMIT 200
        ");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jexit(true, $items);
    }
} catch (Throwable $e) {
    jexit(false, null, $e->getMessage());
}
