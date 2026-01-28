<?php
/**
 * PHP/db.php
 * Centralized Database .env
 */

// --- 1. Load .env variables manually if file exists ---
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Parse KEY=VALUE
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Put environment if not set
            if (!getenv($key)) {
                putenv("$key=$val");
            }
        }
    }
}

// --- 2. Configuration with Fallbacks ---
$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'ams_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; 

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

// --- 3. PDO Connection ---
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Database connection failed. Please check your .env configuration.'
    ]);
    exit;
}