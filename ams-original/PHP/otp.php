<?php
session_start();
$pending = $_SESSION['pending_user'] ?? null;
if (!$pending) {
  header('Location: login.php?err=auth');
  exit;
}

function mask_email($email)
{
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
  [$user, $domain] = explode('@', $email, 2);
  $u = strlen($user) > 2 ? substr($user, 0, 2) . str_repeat('*', max(1, strlen($user) - 2)) : $user[0] . '*';
  return $u . '@' . $domain;
}

// $devOtp    = $_SESSION['otp_code'] ?? null; // Removed for dev mode
$expiresTs = $_SESSION['otp_expires'] ?? 0;
$expiresIn = max(0, $expiresTs - time());

$err = $_GET['err'] ?? '';
$msg = $_GET['msg'] ?? '';
$banner = '';
$cls = 'bg-blue-600';
if ($msg === 'otp_sent') {
  $banner = 'We emailed you a 6-digit code.';
  $cls = 'bg-blue-600';
}
if ($msg === 'otp_resent') {
  $banner = 'We sent a new 6-digit code.';
  $cls = 'bg-blue-600';
}
if ($err === 'expired') {
  $banner = 'The code expired. Please request a new one.';
  $cls = 'bg-red-600';
}
if ($err === 'invalid') {
  $banner = 'Invalid code. Try again.';
  $cls = 'bg-red-600';
}
if ($err === 'auth') {
  $banner = 'Please log in again.';
  $cls = 'bg-red-600';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Enter OTP - CRAD System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="icon" type="image/png" href="../IMAGE/sms.png" />
  <link rel="stylesheet" href="../app/css/auth.css?v=1" />
</head>

<body class="auth-bg min-h-screen flex items-center justify-center font-sans">

  <?php if ($banner): ?>
    <div id="page-banner" class="fixed z-50 top-4 inset-x-0 flex justify-center px-3 pointer-events-none">
      <div class="<?= $cls ?> text-white px-4 py-2 rounded shadow-md pointer-events-auto">
        <?= htmlspecialchars($banner) ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="bg-white bg-opacity-90 rounded-lg shadow-lg w-full max-w-md mx-4 p-8">
    <div class="flex items-center gap-3 mb-6">
      <img src="../IMAGE/sms.png" alt="logo" class="w-10 h-10 object-contain">
      <h1 class="text-xl font-bold text-blue-900">Two-Step Verification</h1>
    </div>

    <p class="text-gray-700 mb-4">
      We sent a 6-digit code to <strong><?= htmlspecialchars(mask_email($pending['email'])) ?></strong>.
    </p>

    <?php if ($err === 'expired'): ?>
      <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm">The code expired. Please request a new one.</div>
    <?php elseif ($err === 'invalid'): ?>
      <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm">Invalid code. Try again.</div>
    <?php endif; ?>

    <form action="otp_verify.php" method="POST" class="space-y-4">
      <div>
        <label for="otp" class="block text-blue-900 font-semibold mb-1">Enter 6-digit code</label>
        <input type="text" id="otp" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
          class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          placeholder="••••••" required>
      </div>
      <button type="submit" class="w-full bg-blue-700 text-white font-semibold py-2 rounded-md hover:bg-blue-800 transition">
        Verify
      </button>
    </form>

    <div class="mt-4 flex items-center justify-between text-sm">
      <form action="otp_resend.php" method="post">
        <button class="text-blue-700 hover:underline" type="submit">Resend code</button>
      </form>
      <a href="login.php" class="text-gray-600 hover:underline">Back to login</a>
    </div>
  </div>

  <script src="../app/js/banner.js?v=1"></script>
  <script src="../app/js/otp.js?v=1"></script>
</body>

</html>