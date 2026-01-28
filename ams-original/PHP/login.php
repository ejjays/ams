<?php
$ok = $_GET['ok'] ?? '';
$okBanner = '';
$okCls = 'bg-green-600';
if ($ok === 'registered') {
  $okBanner = 'Account created successfully! You can now log in.';
}
?>

<?php
session_start();

/* DB config */
$dsn     = "mysql:host=" . (getenv('DB_HOST') ?: '153.92.15.81') . ";dbname=" . (getenv('DB_NAME') ?: 'u514031374_ams') . ";charset=utf8mb4";
$db_user = getenv('DB_USER') ?: 'u514031374_ams';
$db_pass = getenv('DB_PASS') ?: 'amsP@55w0rd';

/* Handle login submit */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // --- START: New Multi-Level Lockout Constants ---
  define('ATTEMPTS_PER_LEVEL', 5);       // 5 attempts bago mag-lock
  define('LOCKOUT_LEVEL_1_DURATION', 60);  // 1 minute (in seconds)
  define('LOCKOUT_LEVEL_2_DURATION', 300); // 5 minutes (in seconds)
  define('WARNING_THRESHOLD_REMAINING', 2); // Simulang mag-warning kapag 2 or 1 na lang ang natitira
  // --- END: New Multi-Level Lockout Constants ---

  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';

  if ($u === '' || $p === '') {
    header('Location: login.php?err=empty');
    exit;
  }

  try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Kunin ang user
    $stmt = $pdo->prepare(
      "SELECT id, username, email, password_hash, role,
              failed_login_attempts, last_failed_login
       FROM users
       WHERE username = :u OR email = :u
       LIMIT 1"
    );
    $stmt->execute([':u' => $u]);
    $user = $stmt->fetch();

    // --- START: New Multi-Level Lockout Check Logic ---
    if ($user) {
      $fails = $user['failed_login_attempts'];
      $last_fail_time = $user['last_failed_login'] ? strtotime($user['last_failed_login']) : 0;
      // Kung hindi pa nag-fail, ituring na "expired" na ang lock
      $time_since_last_fail = $last_fail_time ? (time() - $last_fail_time) : (LOCKOUT_LEVEL_2_DURATION + 1);

      $current_lockout_duration_sec = 0;
      $current_lockout_duration_min = 0;

      // Check kung nasa Level 2 Lockout (10 o higit pang fails)
      if ($fails >= (ATTEMPTS_PER_LEVEL * 2)) {
        $current_lockout_duration_sec = LOCKOUT_LEVEL_2_DURATION;
        $current_lockout_duration_min = 5;
      }
      // Check kung nasa Level 1 Lockout (5 hanggang 9 na fails)
      else if ($fails >= ATTEMPTS_PER_LEVEL) {
        $current_lockout_duration_sec = LOCKOUT_LEVEL_1_DURATION;
        $current_lockout_duration_min = 1;
      }

      // Ngayon, i-check kung NAKA-LOCK pa ba siya base sa oras
      if ($current_lockout_duration_sec > 0 && $time_since_last_fail < $current_lockout_duration_sec) {
        // Naka-lock pa! I-redirect kasama ang tamang duration.
        header("Location: login.php?err=locked&d=$current_lockout_duration_min");
        exit;
      }
    }
    // --- END: New Multi-Level Lockout Check Logic ---

    // Check kung TAMA ang password
    if ($user && password_verify($p, $user['password_hash'])) {

      // --- START: Reset Logic (I-reset ang counter kapag TAMA ang login) ---
      if ($user['failed_login_attempts'] > 0) {
        $resetStmt = $pdo->prepare(
          "UPDATE users SET failed_login_attempts = 0, last_failed_login = NULL WHERE id = :id"
        );
        $resetStmt->execute([':id' => $user['id']]);
      }
      // --- END: Reset Logic ---

      // OTP step (Original code mo)
      $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
      $_SESSION['otp_code']     = $otp;
      $_SESSION['otp_expires']  = time() + 300; // 5 minutes
      $_SESSION['otp_attempts'] = 0;
      $_SESSION['pending_user'] = [
        'id'       => $user['id'],
        'username' => $user['username'],
        'email'    => $user['email'],
        'role'     => $user['role'] ?? 'user'
      ];
      header('Location: otp.php?msg=otp_sent');
      exit;
    }

    // --- START: New Failure/Increment Logic ---
    // Kung MALI ang credentials
    if ($user) {
      // User ay natagpuan, pero MALI ang password.
      // I-increment natin ang counter.
      $failStmt = $pdo->prepare(
        "UPDATE users
         SET failed_login_attempts = failed_login_attempts + 1,
             last_failed_login = NOW()
         WHERE id = :id"
      );
      $failStmt->execute([':id' => $user['id']]);

      // Kunin natin ang BAGONG bilang ng fails
      $new_fail_count = $user['failed_login_attempts'] + 1;

      // Kalkulahin ang natitirang attempts sa level na 'to
      $attempts_in_this_set = $new_fail_count % ATTEMPTS_PER_LEVEL;
      if ($attempts_in_this_set == 0) {
        $attempts_in_this_set = ATTEMPTS_PER_LEVEL; // Ito yung pang-5, 10, 15 na attempt
      }
      $remaining_attempts = ATTEMPTS_PER_LEVEL - $attempts_in_this_set;

      // Check kung itong attempt na 'to ay nag-trigger ng lockout
      if ($new_fail_count == ATTEMPTS_PER_LEVEL) { // Pang-5 attempt
        header('Location: login.php?err=locked&d=1'); // Lock ng 1 minuto
        exit;
      } else if ($new_fail_count == (ATTEMPTS_PER_LEVEL * 2)) { // Pang-10 attempt
        header('Location: login.php?err=locked&d=5'); // Lock ng 5 minuto
        exit;
      }
      // Tandaan: Kahit anong attempt na lampas sa 10 (e.g., 11, 12) ay dadaan
      // sa "Lockout Check" sa taas at makikita na 5-min lock pa rin siya.

      // Check kung mag-wa-warning (e.g., 2 or 1 remaining)
      if ($remaining_attempts > 0 && $remaining_attempts <= WARNING_THRESHOLD_REMAINING) {
        header("Location: login.php?err=warn&rem=$remaining_attempts");
        exit;
      }
    }

    // Default error para sa:
    // 1. Maling username
    // 2. Fails 1, 2 (bago mag-warning)
    // 3. Fails 6, 7 (bago mag-warning ulit)
    header('Location: login.php?err=invalid');
    exit;
    // --- END: New Failure/Increment Logic ---

  } catch (Throwable $e) {
    // Magandang practice na i-log ang error para makita mo
    error_log($e->getMessage());
    header('Location: login.php?err=server');
    exit;
  }
}

/* Banner mapping (NAAYOS NA ITO) */
$err = $_GET['err'] ?? '';
$msg = $_GET['msg'] ?? '';
$banner = '';
$cls = 'bg-blue-600';

if ($err === 'empty') {
  $banner = 'Please fill in both username/email and password.';
  $cls = 'bg-red-600';
} elseif ($err === 'invalid') {
  $banner = 'Username or password is invalid.';
  $cls = 'bg-red-600';
} elseif ($err === 'server') {
  $banner = 'Server error. Please try again.';
  $cls = 'bg-red-600';
} elseif ($err === 'auth') {
  $banner = 'Please log in to continue.';
  $cls = 'bg-red-600';
} elseif ($err === 'locked') {
  $duration = intval($_GET['d'] ?? 1); // Kunin ang duration (in minutes) mula sa URL
  $duration_text = $duration === 1 ? '1 minute' : "$duration minutes"; // Para "1 minute" or "5 minutes"

  $banner = "Too many attempts. Please try again after $duration_text.";
  $cls = 'bg-red-600';
} elseif ($err === 'warn') {
  $rem = intval($_GET['rem'] ?? 0); // Kinukuha natin yung 'remaining attempts' mula sa URL
  $attempt_text = $rem === 1 ? 'attempt' : 'attempts'; // Para maging "1 attempt" or "2 attempts"
  $banner = "Invalid password. You have $rem $attempt_text remaining before your account is locked.";
  $cls = 'bg-orange-500'; // Orange banner

} elseif ($err === 'session_expired') {
  $banner = 'Your session expired due to inactivity. Please log in again.';
  $cls = 'bg-blue-600';
} elseif ($msg === 'registered') {
  $banner = 'Account created. You can log in now.';
  $cls = 'bg-green-600';
} elseif ($msg === 'logged_out') {
  $banner = 'You have been logged out.';
  $cls = 'bg-blue-600';
} elseif ($msg === 'otp_sent') {
  $banner = 'We emailed you a 6-digit code.';
  $cls = 'bg-blue-600';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>School Management System - Login</title>
  <link rel="icon" type="image/png" href="../IMAGE/sms.png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../app/css/auth.css?v=2" />
  <style>
    /* minor refinements for this screen */
    .card-shadow {
      box-shadow: 0 25px 60px rgba(0, 0, 0, .15);
    }
  </style>
</head>

<body class="auth-bg min-h-screen flex items-center justify-center font-sans">
  <?php if (!empty($okBanner)): ?>
    <div class="fixed z-50 top-4 inset-x-0 flex justify-center px-3 pointer-events-none">
      <div class="pointer-events-auto <?= $okCls ?> text-white px-4 py-2 rounded shadow-md" role="status" aria-live="polite">
        <?= htmlspecialchars($okBanner) ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($banner)): ?>
    <div class="fixed z-50 top-4 inset-x-0 flex justify-center px-3 pointer-events-none">
      <div class="pointer-events-auto <?= $cls ?> text-white px-4 py-2 rounded shadow-md" role="alert" aria-live="assertive">
        <?= htmlspecialchars($banner) ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="bg-white/90 backdrop-blur-md rounded-2xl card-shadow max-w-6xl w-full mx-4 grid grid-cols-1 md:grid-cols-2 overflow-hidden border border-white/60">
    <div class="p-10 md:p-12 flex flex-col items-center justify-center text-center bg-gradient-to-br from-white via-blue-50 to-blue-100">
      <img src="../IMAGE/sms.png" alt="School Logo" class="w-28 h-28 mb-6 rounded-full shadow-lg border-4 border-white" />
      <h1 class="text-4xl md:text-5xl font-extrabold mb-3 tracking-tight text-blue-900">WELCOME TO</h1>
      <h2 class="text-5xl font-extrabold text-blue-700 drop-shadow-md leading-tight">SCHOOL<br />MANAGEMENT<br />SYSTEM</h2>
      <p class="mt-6 text-gray-700 max-w-md leading-relaxed">
        Empowering education through a unified academic management system that enhances learning,
        streamlines processes, and connects the academic community.
      </p>
      <!-- UPDATED: Learn More now links to sms.php -->
      <a href="./sms.php" class="mt-8 inline-flex items-center justify-center rounded-lg bg-blue-700 px-6 py-3 font-extrabold text-white hover:bg-blue-800 transition">
        Learn More
      </a>
    </div>

    <div class="bg-white p-10 md:p-12 flex items-center justify-center">
      <form id="loginForm" method="POST" action="login.php" class="w-full max-w-sm">
        <div class="text-center mb-2 -mt-12 md:-mt-18">
          <h3 class="text-4xl md:text-5xl font-black text-blue-800 tracking-tight">ACCREDITATION</h3>
        </div>
        <div class="mt-12 md:mt-20"><label for="username" class="block text-sm font-medium text-blue-900">Username or Email</label>
          <input id="username" name="username" type="text" autocomplete="username"
            class="mt-1 w-full rounded-md border border-gray-300 bg-white/95 px-4 py-2
                       focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600" required />

          <label for="password" class="block text-sm font-medium text-blue-900 mt-4">Password</label>
          <div class="relative">
            <div class="relative" data-toggle="password">
              <input id="password" name="password" type="password" autocomplete="current-password"
                class="mt-1 w-full rounded-md border border-gray-300 bg-white/95 px-4 py-2 pr-14
                         focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-600" required />
              <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center z-10 bg-transparent text-gray-500 hover:text-gray-800 focus:outline-none" aria-label="Show password" aria-pressed="false" aria-controls="password" class="absolute inset-y-0 right-0 px-3 flex items-center z-10 bg-transparent text-gray-500 hover:text-gray-800 focus:outline-none pointer-events-auto" data-eye>
                <span data-icon="show"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <path fill="currentColor" d="M12 4C6.5 4 2.2 7.1 0 12c2.2 4.9 6.5 8 12 8s9.8-3.1 12-8c-2.2-4.9-6.5-8-12-8zm0 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                    <circle cx="12" cy="12" r="2" fill="currentColor" />
                  </svg></span>
                <span data-icon="hide" class="hidden"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                    <path fill="currentColor" d="M12 4C6.5 4 2.2 7.1 0 12c1 2.1 2.4 3.9 4.2 5.3l2-2C4.7 14.1 3.4 13.1 2.4 12c2.2-4 6.1-6 9.6-6 2.2 0 4.4.7 6.4 2.1l1.8-1.8C17.7 4.8 15 4 12 4zM21.6 12c-.9 1.6-2.1 2.9-3.6 4l1.6 1.6c2-1.6 3.6-3.7 4.4-5.6-.8-1.8-2.2-3.8-4.1-5.3l-1.9 1.9c1.6 1.2 2.7 2.3 3.6 3.4z" />
                    <circle cx="12" cy="12" r="4" fill="currentColor" />
                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                  </svg></span>
              </button>
            </div>
            <button type="button" aria-label="Show password"
              class="absolute right-2 top-1/2 -translate-y-1/2 px-2 text-gray-500 hover:text-gray-700"
              data-toggle="password" data-target="#password">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>

          <!-- Forgot Password link (added under the password field, above the Login button) -->
          <div class="mt-2 text-right">
            <a href="forgot_password.php"
              class="text-sm font-semibold text-blue-700 hover:underline focus:outline-none focus:ring-2 focus:ring-blue-500/30 rounded">
              Forgot password?
            </a>
          </div>

          <button type="submit"
            class="mt-6 w-full rounded-md bg-blue-700 py-2.5 font-semibold text-white
                       hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-500/20
                       disabled:opacity-60 disabled:cursor-not-allowed">
            Login
          </button>

          <p class="mt-4 text-center text-gray-500 text-sm">
            Don’t have an account?
            <a href="signup.php" class="text-blue-700 font-semibold hover:underline">Sign up</a>
          </p>
        </div>
      </form>
    </div>
  </div>

  <script src="https://kit.fontawesome.com/a2e0e6ad5b.js" crossorigin="anonymous"></script>

  <script src="../app/js/auth.js?v=2"></script>
  <script>
    (function() {
      try {
        var wrappers = document.querySelectorAll('[data-toggle="password"]');
        if (!wrappers || !wrappers.length) return;
        wrappers.forEach(function(wrapper) {
          var input = wrapper.querySelector('input');
          var btn = wrapper.querySelector('[data-eye]');
          if (!input || !btn) return;
          var iconShow = btn.querySelector('[data-icon="show"]');
          var iconHide = btn.querySelector('[data-icon="hide"]');
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var hidden = input.getAttribute('type') === 'password';
            input.setAttribute('type', hidden ? 'text' : 'password');
            btn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
            if (iconShow && iconHide) {
              if (hidden) {
                iconShow.classList.add('hidden');
                iconHide.classList.remove('hidden');
              } else {
                iconShow.classList.remove('hidden');
                iconHide.classList.add('hidden');
              }
            }
          });
        });
      } catch (e) {
        /* no-op */
      }
    })();
  </script>

  <script>
    (function() {
      // Hintayin na mag-load ang buong page
      document.addEventListener('DOMContentLoaded', function() {
        // Kunin ang mga parameters mula sa URL (e.g., ?err=locked&d=1)
        const params = new URLSearchParams(window.location.search);

        // Check kung ang error ay 'locked'
        if (params.get('err') === 'locked') {
          // Kunin kung ilang minuto ang lock (default sa 1 kung walang 'd')
          const durationInMinutes = parseInt(params.get('d'), 10) || 1;

          // I-calculate ang oras sa milliseconds
          // (e.g., 1 minute * 60 seconds * 1000 ms)
          const durationInMs = durationInMinutes * 60 * 1000;

          // Mag-set ng timer
          setTimeout(function() {
            // Pagkatapos ng lock period (e.g., 1 minuto),
            // i-redirect ang user pabalik sa malinis na login.php.
            // Awtomatikong mawawala ang "Too many attempts..." banner.
            window.location.href = 'login.php';
          }, durationInMs);
        }
      });
    })();
  </script>


  <script>
    (function() {
      // Hintaying mag-load ang page
      document.addEventListener('DOMContentLoaded', function() {

        // Kunin ang PARENT containers ng dalawang banner types
        // Hahanapin nito ang 'div' na may 'role="status"' (para sa success) 
        // O 'div' na may 'role="alert"' (para sa error/info)
        var bannerAlerts = document.querySelectorAll('div[role="status"], div[role="alert"]');

        if (bannerAlerts.length > 0) {
          bannerAlerts.forEach(function(banner) {

            // Ang i-fa-fade natin ay ang pinaka-parent container (yung 'fixed z-50...')
            var container = banner.parentElement;

            if (container) {
              // Lagyan ng transition para sa fade-out effect
              container.style.transition = 'opacity 0.5s ease-out';

              // Mag-set ng timer para sa 5 segundo (5000 milliseconds)
              setTimeout(function() {

                // Simulan ang pag-fade out
                container.style.opacity = '0';

                // Pagkatapos ng 0.5s (dapat tugma sa transition), itago na siya
                setTimeout(function() {
                  container.style.display = 'none';
                }, 500); // 0.5 seconds

              }, 5000); // 5 seconds
            }
          });
        }
      });
    })();
  </script>
</body>

</html>