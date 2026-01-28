<?php
require __DIR__ . '/auth_guard.php'; // Ensure user is authenticated and authorized
require __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
        exit;
    }

    $programCode = trim($data['code'] ?? '');
    $programName = trim($data['name'] ?? '');

    if (empty($programCode) || empty($programName)) {
        echo json_encode(['success' => false, 'message' => 'Program code and name are required.']);
        exit;
    }

    try {
        // Check if program code or name already exists (optional, but good practice)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE name = :name OR code = :code");
        $stmt->execute([':name' => $programName, ':code' => $programCode]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Program with this code or name already exists.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO programs (name, code) VALUES (:name, :code)");
        $stmt->execute([':name' => $programName, ':code' => $programCode]);

        echo json_encode(['success' => true, 'message' => 'Program created successfully!', 'id' => $pdo->lastInsertId()]);
        exit;
    } catch (PDOException $e) {
        error_log("Error creating program: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}
