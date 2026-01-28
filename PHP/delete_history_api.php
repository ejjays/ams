<?php
require __DIR__ . '/auth_guard.php'; // session
require __DIR__ . '/db.php'; // $pdo

header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $err = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

$action = $_GET['action'] ?? 'list';
$docsDir = realpath(__DIR__ . '/../uploads/documents');

// ---------- LIST HISTORY ----------
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT 
                h.id AS history_id, -- Important: gamitin ang history ID
                h.title, 
                h.original_name, 
                h.deleted_at,
                CONCAT(u.first_name, ' ', u.last_name) AS deleted_by_name
            FROM document_delete_history h
            LEFT JOIN users u ON u.id = h.deleted_by_user_id
            ORDER BY h.deleted_at DESC
            LIMIT 100
        ");
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jexit(true, $history);
    } catch (Throwable $e) {
        jexit(false, null, $e->getMessage());
    }
}

// ---------- RESTORE (FINAL AT KOMPREHENSIBONG FIX) ----------
if ($action === 'restore') {
    $history_id = (int)($_GET['id'] ?? 0);
    if ($history_id <= 0) jexit(false, null, 'Missing history ID.');

    // 1. Kunin ang record mula sa history
    $stmt = $pdo->prepare("SELECT * FROM document_delete_history WHERE id = :id");
    $stmt->execute([':id' => $history_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jexit(false, null, 'History record not found.');

    // KUNIN at I-VALIDATE ang original owner ID
    $originalOwnerId = (int)$row['owner_user_id'];

    $checkOwner = $pdo->prepare("SELECT id FROM users WHERE id = :owner_id");
    $checkOwner->execute([':owner_id' => $originalOwnerId]);

    if (!$checkOwner->fetch()) {
        // SCENARIO 1: Kung ang original owner ay na-delete na, i-assign sa kasalukuyang user.
        $ownerToUse = $userId;
    } else {
        // SCENARIO 2: Valid pa ang owner, gamitin ang original owner ID.
        $ownerToUse = $originalOwnerId;
    }

    // 2. Ibalik sa 'documents' table - Gagamitin ang ownerToUse na na-validate
    try {
        $restoreStmt = $pdo->prepare("
            INSERT INTO documents 
                (id, owner_user_id, title, comment, original_name, stored_name, 
                 file_ext, mime_type, file_size, created_at, archived_at)
            VALUES 
                (NULL, :owner_user_id, :title, :comment, :original_name, :stored_name, 
                 :file_ext, :mime_type, :file_size, :created_at, NULL)
        ");
        $restoreStmt->execute([
            ':owner_user_id' => $ownerToUse, // Gagamitin ang VALIDATED ID dito
            ':title' => $row['title'],
            ':comment' => $row['comment'],
            ':original_name' => $row['original_name'],
            ':stored_name' => $row['stored_name'],
            ':file_ext' => $row['file_ext'],
            ':mime_type' => $row['mime_type'],
            ':file_size' => $row['file_size'],
            ':created_at' => $row['created_at']
        ]);
    } catch (Throwable $e) {
        // Kung may ibang error pa, ipapakita ang detalye nito.
        jexit(false, null, 'Restore failed: ' . $e->getMessage());
    }

    // 3. Burahin sa history table
    $pdo->prepare("DELETE FROM document_delete_history WHERE id = :id")->execute([':id' => $history_id]);
    jexit(true, ['restored' => true]);
}


// ---------- PERMANENT DELETE ----------
if ($action === 'perm_delete') {
    $history_id = (int)($_GET['id'] ?? 0);
    if ($history_id <= 0) jexit(false, null, 'Missing history ID.');

    // 1. Kunin ang stored_name bago burahin
    $stmt = $pdo->prepare("SELECT stored_name FROM document_delete_history WHERE id = :id");
    $stmt->execute([':id' => $history_id]);
    $stored_name = $stmt->fetchColumn();
    if (!$stored_name) jexit(false, null, 'History record not found.');

    // 2. Burahin ang record sa history
    $pdo->prepare("DELETE FROM document_delete_history WHERE id = :id")->execute([':id' => $history_id]);

    // 3. Burahin ang actual file sa disk
    if (is_file($docsDir . '/' . $stored_name)) {
        @unlink($docsDir . '/' . $stored_name);
    }
    jexit(true, ['deleted' => true]);
}

jexit(false, null, 'Invalid action.');
