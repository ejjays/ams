<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $err = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
    exit;
}

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) jexit(false, null, 'Not authenticated.');

$action = $_GET['action'] ?? 'list';

// ---------- LIST AUDIT LOGS ----------
if ($action === 'list') {
    try {
        $stmt = $pdo->query("
            SELECT 
                a.id,
                a.action,
                a.module,
                a.description,
                a.created_at,
                CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                u.role
            FROM audit_logs a
            LEFT JOIN users u ON u.id = a.user_id
            ORDER BY a.created_at DESC
            LIMIT 200
        ");
        jexit(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Throwable $e) {
        jexit(false, null, $e->getMessage());
    }
}

jexit(false, null, 'Invalid action.');
