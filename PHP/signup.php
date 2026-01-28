<?php
session_start();

/* Banner build from query */
$err = $_GET['err'] ?? '';
$banner = '';
$cls = 'bg-blue-600';
if ($err === 'empty') {
  $banner = 'Please complete all required fields.';
  $cls = 'bg-red-600';
} elseif ($err === 'bademail') {
  $banner = 'Please enter a valid email address.';
  $cls = 'bg-red-600';
} elseif ($err === 'nomatch') {
  $banner = 'Passwords did not match.';
  $cls = 'bg-red-600';
} elseif ($err === 'weakpwd') {
  // ===== START: BINAGONG ERROR MESSAGE =====
  $banner = 'Password must be 8+ chars and include uppercase, lowercase, number, and special character.';
  // ===== END: BINAGONG ERROR MESSAGE =====
  $cls = 'bg-red-600';
} elseif ($err === 'taken') {
  $banner = 'Username or email already exists.';
  $cls = 'bg-red-600';
} elseif ($err === 'server') {
  $banner = 'Server error. Please try again.';
  $cls = 'bg-red-600';
} elseif ($err === 'terms') {
  $banner = 'Please accept the Terms and Privacy Policy.';
  $cls = 'bg-red-600';
} elseif ($err === 'baduser') {
  $banner = 'Username must be 3+ chars and use letters/numbers/._ only.';
  $cls = 'bg-red-600';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/png" href="../IMAGE/sms.png" />
  <title>School Management System - Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="../app/css/auth.css?v=1" />
</head>

<body class="auth-bg min-h-screen flex items-center justify-center font-sans p-4">

  <?php if ($banner): ?>
    <div id="page-banner" class="fixed z-50 top-4 inset-x-0 flex justify-center px-3 pointer-events-none">
      <div class="<?= $cls ?> text-white px-4 py-2 rounded shadow-md pointer-events-auto">
        <?= htmlspecialchars($banner) ?>
      </div>
    </div>
  <?php endif; ?>


  <div class="bg-gray-50 rounded-lg shadow-lg max-w-lg w-full mx-4 p-6">

    <div class="flex items-center gap-3 mb-3">
      <img src="../IMAGE/sms.png" alt="School Logo" class="w-10 h-10 object-contain" />
      <h3 class="text-2xl font-bold text-blue-900">Sign up</h3>
    </div>

    <form id="signupForm" action="register.php" method="POST" class="space-y-3">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="first_name" class="block text-gray-700 font-semibold mb-1">First name</label>
          <input type="text" id="first_name" name="first_name"
            class="w-full border border-gray-300 rounded-md px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
        <div>
          <label for="last_name" class="block text-gray-700 font-semibold mb-1">Last name</label>
          <input type="text" id="last_name" name="last_name"
            class="w-full border border-gray-300 rounded-md px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
        </div>
      </div>

      <div>
        <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
        <input type="email" id="email" name="email"
          class="w-full border border-gray-300 rounded-md px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
          required />
      </div>

      <div>
        <label for="username" class="block text-gray-700 font-semibold mb-1">Username</label>
        <input type="text" id="username" name="username"
          class="w-full border border-gray-300 rounded-md px-4 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
          required />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="relative">
          <label for="password" class="block text-gray-700 font-semibold mb-1">Password</label>
          <input type="password" id="password" name="password" minlength="8"
            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':\\|,.<>\/?]).{8,}"
            title="Must be 8+ chars and include uppercase, lowercase, number, and special character."
            class="w-full border border-gray-300 rounded-md px-4 py-1.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
          <span class="absolute right-3 top-8 cursor-pointer text-gray-500 hover:text-gray-700"
            data-toggle="password" data-target="#password" data-icon="#eyeIcon1">
            <i id="eyeIcon1" class="fa-solid fa-eye"></i>
          </span>

          <div id="password-reqs" class="mt-2 text-xs text-gray-500 space-y-1">
            <p id="req-length" class="req-item">
              <i class="req-icon fa-solid fa-circle-xmark text-red-500"></i>
              <span>At least 8 characters</span>
            </p>
            <p id="req-lower" class="req-item">
              <i class="req-icon fa-solid fa-circle-xmark text-red-500"></i>
              <span>At least one lowercase letter</span>
            </p>
            <p id="req-upper" class="req-item">
              <i class="req-icon fa-solid fa-circle-xmark text-red-500"></i>
              <span>At least one uppercase letter</span>
            </p>
            <p id="req-number" class="req-item">
              <i class="req-icon fa-solid fa-circle-xmark text-red-500"></i>
              <span>At least one number</span>
            </p>
            <p id="req-special" class="req-item">
              <i class="req-icon fa-solid fa-circle-xmark text-red-500"></i>
              <span>At least one special character</span>
            </p>
          </div>
        </div>

        <div class="relative">
          <label for="confirm_password" class="block text-gray-700 font-semibold mb-1">Confirm password</label>
          <input type="password" id="confirm_password" name="confirm_password" minlength="8"
            class="w-full border border-gray-300 rounded-md px-4 py-1.5 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500"
            required />
          <span class="absolute right-3 top-8 cursor-pointer text-gray-500 hover:text-gray-700"
            data-toggle="password" data-target="#confirm_password" data-icon="#eyeIcon2">
            <i id="eyeIcon2" class="fa-solid fa-eye"></i>
          </span>
          <p id="confirm-pass-msg" class="mt-1 text-xs"></p>
        </div>
      </div>

      <div class="flex items-start gap-3">
        <input id="terms" name="terms" type="checkbox" class="mt-1" required />
        <label for="terms" class="text-sm text-gray-700">
          I agree to the
          <a href="terms.php" target="_blank" class="text-blue-600 hover:underline">Terms</a>
          and
          <a href="privacy.php" target="_blank" class="text-blue-600 hover:underline">Privacy Policy</a>.
        </label>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300">
        Create Account
      </button>
    </form>

    <p class="mt-3 text-center text-gray-600 text-sm">
      Already have an account?
      <a href="login.php" class="text-blue-600 hover:underline">Log in</a>
    </p>
  </div>


  <script src="https://cdn.tailwindcss.com"></script>
  <script src="../app/js/banner.js?v=1"></script>
  <script src="../app/js/auth.js?v=1"></script>
</body>

</html>