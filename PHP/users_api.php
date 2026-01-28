<?php
// PHP/users_api.php (v1.1 - Minor fix in PUT validation)
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php'; // Uses the correct $pdo from db.php

header('Content-Type: application/json; charset=utf-8');

// Define allowed roles (Make sure this matches ENUM/checks elsewhere)
$ALLOWED_ROLES = ['admin', 'dean', 'program_coordinator', 'faculty', 'staff', 'external_accreditor'];


function json_fail($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}
function json_ok($data = null)
{
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
function clean($v)
{
    // Ensure it's treated as a string before trimming
    return trim((string)$v);
}
function valid_username($u)
{
    // Allow letters, numbers, dot, underscore, minimum 3 chars
    return (bool)preg_match('/^[A-Za-z0-9._]{3,}$/', $u);
}

$method = $_SERVER['REQUEST_METHOD'];

// ---------- GET: list/search ----------
if ($method === 'GET') {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    try {
        if ($q !== '') {
            // Use prepared statement for search query
            $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, username, role
        FROM users
        WHERE CONCAT_WS(' ', first_name, last_name, email, username) LIKE :query
        ORDER BY id DESC
        LIMIT 300
      ");
            $stmt->execute([':query' => '%' . $q . '%']);
        } else {
            // Fetch all users if no search query
            $stmt = $pdo->query("
        SELECT id, first_name, last_name, email, username, role
        FROM users
        ORDER BY id DESC
        LIMIT 300
      ");
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_ok($rows);
    } catch (Throwable $e) {
        // Log error for debugging, return generic message
        error_log("User List Error: " . $e->getMessage());
        json_fail('Server error while fetching users.', 500);
    }
}

// ---------- POST: create ----------
if ($method === 'POST') {
    // Collect data using clean() helper
    $first = clean($_POST['first_name'] ?? '');
    $last  = clean($_POST['last_name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $user  = clean($_POST['username'] ?? '');
    $role  = strtolower(clean($_POST['role'] ?? 'faculty')); // Default to faculty
    $pass  = (string)($_POST['password'] ?? ''); // Password needs to be string

    // --- Validation ---
    if ($first === '' || $last === '' || $email === '' || $user === '') {
        json_fail('Please complete all required fields.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_fail('Invalid email address format.');
    }
    if (!valid_username($user)) {
        json_fail('Username must be 3+ chars; letters, numbers, dot or underscore only.');
    }
    if (!in_array($role, $ALLOWED_ROLES, true)) {
        // Fallback to default if somehow an invalid role is sent
        $role = 'faculty';
        // Optionally: json_fail('Invalid role selected.');
    }
    if ($pass === '' || strlen($pass) < 8) {
        json_fail('Password is required and must be at least 8 characters for new users.');
    }

    try {
        // Check for uniqueness before inserting
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
        $chk->execute([':email' => $email, ':username' => $user]);
        if ((int)$chk->fetchColumn() > 0) {
            json_fail('Username or email already exists.');
        }

        // Hash the password securely
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Insert new user
        $ins = $pdo->prepare("
      INSERT INTO users (first_name, last_name, email, username, role, password_hash)
      VALUES (:first, :last, :email, :username, :role, :pw)
    ");
        $ins->execute([
            ':first' => $first,
            ':last'  => $last,
            ':email' => $email,
            ':username' => $user,
            ':role'  => $role,
            ':pw'    => $hash
        ]);

        $id = (int)$pdo->lastInsertId();
        json_ok(['id' => $id]); // Respond with the new user ID
    } catch (PDOException $e) {
        error_log("User Create PDO Error: " . $e->getMessage());
        // Handle potential duplicate entry race condition if needed, otherwise generic error
        if (($e->errorInfo[1] ?? 0) == 1062) {
            json_fail('Username or email already taken (concurrent request).');
        } else {
            json_fail('Database error during user creation.', 500);
        }
    } catch (Throwable $e) {
        error_log("User Create Error: " . $e->getMessage());
        json_fail('Server error during user creation.', 500);
    }
}

// ---------- PUT: update (expects JSON) ----------
if ($method === 'PUT') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: []; // Ensure $data is an array

    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) json_fail('Invalid or missing user ID.');

    // --- Prepare update ---
    $sets = []; // To store "column = :placeholder" parts
    $params = [':id' => $id]; // Parameters for PDO execute

    // Validate and add fields to update if they exist in the payload
    if (isset($data['first_name'])) {
        $v = clean($data['first_name']);
        if ($v === '') json_fail('First name cannot be empty.');
        $sets[] = 'first_name = :first';
        $params[':first'] = $v;
    }
    if (isset($data['last_name'])) {
        $v = clean($data['last_name']);
        if ($v === '') json_fail('Last name cannot be empty.');
        $sets[] = 'last_name = :last';
        $params[':last'] = $v;
    }
    if (isset($data['email'])) {
        $v = clean($data['email']);
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) json_fail('Invalid email address format.');
        // Check uniqueness excluding the current user
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND id != :id");
        $chk->execute([':email' => $v, ':id' => $id]);
        if ((int)$chk->fetchColumn() > 0) json_fail('Email address is already in use by another user.');
        $sets[] = 'email = :email';
        $params[':email'] = $v;
    }
    if (isset($data['username'])) {
        $v = clean($data['username']);
        if (!valid_username($v)) json_fail('Invalid username format.');
        // Check uniqueness excluding the current user
        $chk = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
        $chk->execute([':username' => $v, ':id' => $id]);
        if ((int)$chk->fetchColumn() > 0) json_fail('Username is already in use by another user.');
        $sets[] = 'username = :username';
        $params[':username'] = $v;
    }
    // *** CRITICAL CHANGE HERE ***
    // Use array_key_exists for role because 'faculty' might be sent unintentionally otherwise
    if (array_key_exists('role', $data)) {
        $v = strtolower(clean($data['role']));
        // Explicitly check if the received role is in the allowed list
        if (!in_array($v, $ALLOWED_ROLES, true)) {
            // Instead of failing, maybe log a warning and DO NOT update the role?
            // Or strictly fail:
            json_fail('Invalid role specified: ' . htmlspecialchars($data['role'])); // More informative error
            // For now, let's allow the update only if the role is valid
        } else {
            $sets[] = 'role = :role';
            $params[':role'] = $v;
        }
    }
    // Only update password if a non-empty string is provided
    if (isset($data['password']) && $data['password'] !== '') {
        $v = (string)$data['password']; // Ensure it's a string
        if (strlen($v) < 8) {
            json_fail('Password must be at least 8 characters if provided.');
        }
        $sets[] = 'password_hash = :pw';
        $params[':pw'] = password_hash($v, PASSWORD_DEFAULT);
    }

    // Check if there's anything to update
    if (empty($sets)) {
        json_fail('No valid fields provided for update.');
    }

    // --- Execute update ---
    $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id LIMIT 1";

    try {
        $upd = $pdo->prepare($sql);
        $upd->execute($params);
        // Check if any row was actually affected
        if ($upd->rowCount() > 0) {
            json_ok(['id' => $id, 'updated' => true]);
        } else {
            // This might happen if the user didn't change anything or ID doesn't exist
            // Check if user exists to differentiate
            $chkUser = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = :id");
            $chkUser->execute([':id' => $id]);
            if ($chkUser->fetchColumn() > 0) {
                json_ok(['id' => $id, 'updated' => false, 'message' => 'No changes detected.']);
            } else {
                json_fail('User not found.', 404);
            }
        }
    } catch (PDOException $e) {
        error_log("User Update PDO Error: " . $e->getMessage());
        if (($e->errorInfo[1] ?? 0) == 1062) { // Duplicate entry error code
            json_fail('Update failed due to duplicate username or email.');
        } else {
            json_fail('Database error during user update.', 500);
        }
    } catch (Throwable $e) {
        error_log("User Update Error: " . $e->getMessage());
        json_fail('Server error during user update.', 500);
    }
}


// ---------- DELETE: remove ----------
if ($method === 'DELETE') {
    // Get ID from query parameter
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) json_fail('Invalid or missing user ID.');

    // Prevent deleting own account? (Optional check)
    // if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    //     json_fail('Cannot delete your own account.', 403);
    // }

    try {
        $del = $pdo->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
        $del->execute([':id' => $id]);
        // Check if a row was actually deleted
        if ($del->rowCount() > 0) {
            json_ok(['id' => $id, 'deleted' => true]);
        } else {
            json_fail('User not found or already deleted.', 404);
        }
    } catch (Throwable $e) {
        error_log("User Delete Error: " . $e->getMessage());
        json_fail('Server error during user deletion.', 500);
    }
}

// Fallback for unsupported methods
json_fail('Method not allowed.', 405);
