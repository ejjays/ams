<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. UNA: I-check kung naka-login ba talaga
if (empty($_SESSION['user_id'])) {
    // Kung hindi naka-login, paalisin
    header('Location: login.php?err=auth');
    exit;
}

// 2. PANGALAWA: Kung naka-login, i-check kung nag-timeout na
$timeout_duration_seconds = 900; // 15 minuto (15 * 60 = 900)

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration_seconds) {

    // Kung nag-timeout, sirain ang session
    session_unset();
    session_destroy();

    // I-redirect sa login page na may bagong error message
    header("Location: login.php?err=session_expired");
    exit;
}

// 3. PANGATLO: Kung HINDI nag-timeout, i-update ang "last activity" time
// Ito ang nagsasabi na "Okay, active pa ang user"
$_SESSION['last_activity'] = time();


// 4. PANG-APAT: Ito yung original code mo para sa mabilis na AJAX calls
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
