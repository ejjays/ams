<?php
// compliance_api.php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $data = null, $err = null)
{
    echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $err]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: List Programs with Compliance Status
if ($method === 'GET') {
    try {
        // Fetch programs with their accreditation status
        // We use the new 'compliance_status' column
        $stmt = $pdo->query("
            SELECT 
                p.id, 
                p.code, 
                p.name, 
                COALESCE(pa.level, 'Candidate') as level, 
                COALESCE(pa.phase, 'Phase 1') as phase, 
                COALESCE(pa.status, 'active') as status,
                COALESCE(pa.compliance_status, 'Compliant') as compliance_status
            FROM programs p
            LEFT JOIN program_accreditation pa ON pa.program_id = p.id
            WHERE p.is_archived = 0
            ORDER BY p.code ASC
        ");

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jexit(true, $data);
    } catch (Throwable $e) {
        jexit(false, null, $e->getMessage());
    }
}

// POST: Update Compliance Status (For Admin/Accreditor to set status)
if ($method === 'POST') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['compliance_status'] ?? '';

    if (!$id || !in_array($status, ['Compliant', 'Minor Deficiency', 'Major Deficiency'])) {
        jexit(false, null, 'Invalid Input');
    }

    try {
        // Upsert logic (Insert if not exists, Update if exists)
        $stmt = $pdo->prepare("
            INSERT INTO program_accreditation (program_id, compliance_status) 
            VALUES (:pid, :stat)
            ON DUPLICATE KEY UPDATE compliance_status = :stat
        ");
        $stmt->execute([':pid' => $id, ':stat' => $status]);
        jexit(true, ['message' => 'Status updated']);
    } catch (Throwable $e) {
        jexit(false, null, $e->getMessage());
    }
}
