<?php
// index.php — send visitors to the landing page (SMS)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
$target = preg_replace('#/{2,}#', '/', $base . '/PHP/sms.php');

// Use 302 while testing to avoid sticky 301 cache
header('Location: ' . $target, true, 302);
exit;
