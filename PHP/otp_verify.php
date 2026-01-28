<?php
session_start();
if (!isset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'])) {
    header('Location: login.php?err=auth');
    exit;
}

$code     = trim($_POST['otp'] ?? '');
// $expected = $_SESSION['otp_code']; // Original code
$expires  = $_SESSION['otp_expires'];
$attempts = $_SESSION['otp_attempts'] ?? 0;

// ===== START: FIXED OTP CODE =====
// Ginawang hardcoded ang "expected" code
$expected = '041102'; // Pansamantalang fixed OTP code
// ===== END: FIXED OTP CODE =====

if (time() > $expires) {
    unset($_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);
    header('Location: otp.php?err=expired');
    exit;
}
if (!preg_match('/^\d{6}$/', $code)) {
    $_SESSION['otp_attempts'] = $attempts + 1;
    header('Location: otp.php?err=invalid');
    exit;
}
if (hash_equals($expected, $code)) {

    // --- ITO ANG NAG-ESTABLISH NG LOGIN ---
    $pending = $_SESSION['pending_user'];
    $_SESSION['user_id']    = $pending['id'];
    $_SESSION['username']   = $pending['username'];
    $_SESSION['role']       = $pending['role'] ?? 'faculty';
    $_SESSION['first_name'] = $pending['first_name'] ?? '';
    $_SESSION['last_name']  = $pending['last_name'] ?? '';
    $_SESSION['full_name']  = trim(($pending['first_name'] ?? '') . ' ' . ($pending['last_name'] ?? ''));

    // --- ITO ANG DINAGDAG NATIN ---
    // Itinatakda ang oras ng simula ng kanyang activity
    $_SESSION['last_activity'] = time();
    // --- END ---

    // Clean up temporary session vars
    unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);

    header('Location: dashboard.php');
    exit;
}

// Kung mali ang code
$_SESSION['otp_attempts'] = $attempts + 1;
if ($_SESSION['otp_attempts'] >= 5) {
    unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);
    header('Location: login.php?err=locked');
    exit;
}
header('Location: otp.php?err=invalid');
exit;
