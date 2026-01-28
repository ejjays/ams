<?php
// register.php — signup handler using the correct db.php connection

// Start session if not already started (needed for potential future login after register)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// *** CRITICAL CHANGE: Use the centralized db.php for connection ***
require __DIR__ . '/db.php'; // Provides the correct $pdo object

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if accessed directly via GET
    header('Location: signup.php');
    exit;
}

/* ---- Collect & validate inputs ---- */
$first   = trim($_POST['first_name'] ?? '');
$last    = trim($_POST['last_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$user    = trim($_POST['username'] ?? '');
$pwd     = (string)($_POST['password'] ?? '');
$pwd2    = (string)($_POST['confirm_password'] ?? '');
$role_in = (string)($_POST['role'] ?? ''); // Get role from form
$terms   = isset($_POST['terms']); // Check if terms checkbox was checked

// Basic validation checks
if ($first === '' || $last === '' || $email === '' || $user === '' || $pwd === '' || $pwd2 === '') {
    header('Location: signup.php?err=empty'); // Redirect with error
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: signup.php?err=bademail');
    exit;
}
if ($pwd !== $pwd2) {
    header('Location: signup.php?err=nomatch');
    exit;
}

// ===== START: BINAGONG STRONG PASSWORD CHECK =====
// Ito ang regex na tumutugma sa rules sa auth.js
$regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/';
if (!preg_match($regex, $pwd)) {
    // Gagamitin nito ang 'err=weakpwd', at 'signup.php' na ang bahalang magpakita
    // ng error message na "Password must be 8+ chars and include..."
    header('Location: signup.php?err=weakpwd');
    exit;
}
// ===== END: BINAGONG STRONG PASSWORD CHECK =====

// Validate username format (letters, numbers, dot, underscore, 3+ chars)
if (!preg_match('/^[A-Za-z0-9._]{3,}$/', $user)) {
    header('Location: signup.php?err=baduser');
    exit;
}
if (!$terms) {
    header('Location: signup.php?err=terms');
    exit;
}

/* Normalize role - default to 'faculty' if empty or invalid */
$role = strtolower(trim($role_in));
// Use the same allowed roles as users_api.php
$allowed_roles = ['admin', 'dean', 'program_coordinator', 'faculty', 'staff', 'external_accreditor'];
if ($role === '' || !in_array($role, $allowed_roles, true)) {
    $role = 'faculty'; // Set default if empty or not allowed
}


/* ---- Connect to MySQL is NO LONGER NEEDED HERE ---- */
/* The $pdo object is already available from db.php      */


try {
    // ---- Ensure `users` table exists (Optional but safe) ----
    // This CREATE TABLE IF NOT EXISTS is good practice here
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      first_name VARCHAR(120) NOT NULL,
      last_name  VARCHAR(120) NOT NULL,
      email      VARCHAR(255) NOT NULL,
      username   VARCHAR(120) NOT NULL,
      password_hash VARCHAR(255) NOT NULL,
      role ENUM('admin','dean','program_coordinator','faculty','staff','external_accreditor') NOT NULL DEFAULT 'faculty',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id),
      UNIQUE KEY uq_users_email (email),
      UNIQUE KEY uq_users_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ");

    // ---- Check for existing email or username before inserting ----
    $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
    $chk->execute([':email' => $email, ':username' => $user]);
    if ((int)$chk->fetchColumn() > 0) {
        header('Location: signup.php?err=taken'); // Redirect with specific error
        exit;
    }

    // ---- Insert user ----
    $hash = password_hash($pwd, PASSWORD_DEFAULT); // Securely hash the password

    $stmt = $pdo->prepare("
    INSERT INTO users (first_name,last_name,email,username,password_hash,role)
    VALUES (:f,:l,:e,:u,:ph,:r)
  ");
    $stmt->execute([
        ':f' => $first,
        ':l' => $last,
        ':e' => $email,
        ':u' => $user,
        ':ph' => $hash,
        ':r' => $role // Use the validated/defaulted role
    ]);

    // Success -> redirect to login with a success message
    header('Location: login.php?ok=registered');
    exit;
} catch (PDOException $e) {
    // Log the detailed error for server admin
    error_log("Signup PDOException: " . $e->getMessage());
    // Redirect with a generic server error for the user
    header('Location: signup.php?err=server');
    exit;
} catch (Throwable $e) {
    // Catch any other unexpected errors
    error_log("Signup Throwable: " . $e->getMessage());
    header('Location: signup.php?err=server');
    exit;
}
