<?php
/**
 * PHP/db.php
 * Centralized Database Connection with .env support.
 */

// --- 1. Load .env variables from Project Root ---
$rootEnv = dirname(__DIR__) . '/.env';
if (file_exists($rootEnv)) {
    $lines = file($rootEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if (!getenv($key)) putenv("$key=$val");
        }
    }
}

// --- 2. Configuration ---
$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'ams_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; 

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database connection failed. Check your .env configuration.']);
    exit;
}
