<?php
/*
* FILE: PHP/db.php
* UPDATED: This version is simplified to use your correct, hardcoded database
* credentials and remove the .env parsing, which was causing the 'root@localhost' error.
*/

$host = '153.92.15.81';
$name = 'u514031374_ams';
$user = 'u514031374_ams';
$pass = 'amsP@55w0rd'; // Your password from the .env file

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $e) {
    // Stop the script with a clear JSON error so you see it in the browser
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'data' => null,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}
