<?php
session_start();
$pending = $_SESSION['pending_user'] ?? null;
if (!$pending) {
    header('Location: login.php?err=auth');
    exit;
}

$otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$_SESSION['otp_code']     = $otp;
$_SESSION['otp_expires']  = time() + 300;
$_SESSION['otp_attempts'] = 0;

// Send via email here if configured
header('Location: otp.php?msg=otp_resent');
exit;
