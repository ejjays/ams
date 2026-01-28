<?php
// public_view.php
// WALANG AUTH_GUARD. Ito ay public.

require __DIR__ . '/db.php'; // $pdo

// 1. Kunin at i-sanitize ang file name
$storedName = $_GET['file'] ?? '';
// KRITIKAL: Iwasan ang directory traversal attacks
$storedName = basename($storedName);

if (empty($storedName)) {
    http_response_code(400);
    echo "Missing file.";
    exit;
}

// 2. Hanapin ang file sa database
try {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE stored_name = :name");
    $stmt->execute([':name' => $storedName]);
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        echo "File record not found.";
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
    exit;
}

// 3. Kunin ang file path
$uploadDir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');
$docsDir = rtrim($uploadDir, '/\\') . '/documents';
$path = $docsDir . '/' . $doc['stored_name'];

if (!is_file($path)) {
    http_response_code(404);
    echo "File missing on server storage.";
    exit;
}

// 4. I-serve ang file (importante ang 'inline')
header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . basename($doc['original_name']) . '"');
header('Content-Length: ' . (string)filesize($path));
header('Access-Control-Allow-Origin: *'); // Para sa viewer services
readfile($path);
exit;
