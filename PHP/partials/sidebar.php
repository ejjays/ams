<?php
// PHP/partials/sidebar.php (fixed, non-scrolling, reordered, collapsible, smooth)

if (!isset($current)) {
  $current = basename($_SERVER['PHP_SELF']);
}

if (!function_exists('active')) {
  function active($page, $current)
  {
    return $current === $page ? 'active' : '';
  }
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$displayName = 'User';
$roleKey     = $_SESSION['role']     ?? '';
$userId      = $_SESSION['user_id']  ?? null;
$username    = $_SESSION['username'] ?? '';

try {
  require_once __DIR__ . '/../db.php';
  if ($userId ?? null) {
    $st = $pdo->prepare("SELECT first_name, last_name, role FROM users WHERE id = ? LIMIT 1");
    $st->execute([$userId]);
    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $full = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
      if ($full !== '') $displayName = $full;
      $roleKey = $row['role'] ?? $roleKey;
    }
  } elseif ($username) {
    $displayName = $username;
  }
} catch (Throwable $e) { /* fall back silently */
}

$labels = [
  'admin'               => 'Admin',
  'dean'                => 'Dean',
  'program_coordinator' => 'Coordinator',
  'faculty'             => 'Faculty',
  'staff'               => 'Staff',
  'external_accreditor' => 'External Accreditor',
  // legacy fallbacks
  'user'                      => 'Faculty',
  'department_head'           => 'Dean',
  'accreditation_committee'   => 'Staff',
  'school_accreditation_team' => 'Staff',
  'school_administrator'      => 'Admin',
  'internal_accreditor'       => 'Staff',
];

$prettyRole = $labels[$roleKey] ?? ($roleKey ? ucwords(str_replace('_', ' ', $roleKey)) : 'Role');
?>

<style>
  :root {
    --sidebar-width: 16rem;
    /* Tailwind w-64 */
    --sb-dur: 280ms;
    /* animation duration */
    --sb-ease: cubic-bezier(.22, 1, .36, 1);
    /* ease-out expo-ish */
  }

  #sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: var(--sidebar-width);
    height: 100vh;
    overflow-y: hidden;
    /* sidebar never scrolls */
    z-index: 40;
    background: inherit;
    transform: translateX(0);
    transition: transform var(--sb-dur) var(--sb-ease), box-shadow 200ms ease;
    will-change: transform;
  }

  /* Page offset when sidebar is visible */
  body {
    margin-left: var(--sidebar-width);
    transition: margin-left var(--sb-dur) var(--sb-ease);
  }

  /* Collapsed: sidebar slides out, content goes full width */
  body.sidebar-collapsed {
    margin-left: 0;
  }

  body.sidebar-collapsed #sidebar {
    transform: translateX(-100%);
  }

  /* Floating burger to reopen when collapsed (with fade/slide) */
  #sidebarFloatingToggle {
    position: fixed;
    top: .75rem;
    left: .75rem;
    z-index: 50;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .5rem;
    display: none;
    align-items: center;
    justify-content: center;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .06), 0 1px 3px rgba(0, 0, 0, .1);
    cursor: pointer;
    opacity: 0;
    transform: translateX(-8px);
    transition: opacity var(--sb-dur) var(--sb-ease), transform var(--sb-dur) var(--sb-ease);
  }

  body.sidebar-collapsed #sidebarFloatingToggle {
    display: inline-flex;
    opacity: 1;
    transform: translateX(0);
  }

  /* Smooth caret rotation for <details> */
  .sidebar-group .caret {
    transition: transform var(--sb-dur) var(--sb-ease);
  }

  /* Respect users who prefer reduced motion */
  @media (prefers-reduced-motion: reduce) {

    #sidebar,
    body,
    #sidebarFloatingToggle,
    .sidebar-group .caret {
      transition: none !important;
    }
  }

  /* Dark-mode safety for the floating button */
  @media (prefers-color-scheme: dark) {
    #sidebarFloatingToggle {
      background: #0f172a;
      color: #e5e7eb;
    }
  }
</style>

<button id="sidebarFloatingToggle" class="text-xl" aria-controls="sidebar" aria-expanded="false" title="Open menu">
  <i class="fa-solid fa-bars"></i>
</button>

<aside
  id="sidebar"
  data-build="dropdown-v2-details-no-overview"
  class="sidebar-shell w-64 border-r flex flex-col h-screen inset-y-0 left-0 overflow-y-hidden z-40"
  role="navigation"
  aria-label="Primary">
  <div class="flex items-center gap-3 px-5 py-4 border-b">
    <button id="sidebarToggle" class="text-xl text-gray-500" aria-controls="sidebar" aria-expanded="true" aria-label="Toggle sidebar">
      <i class="fa-solid fa-bars"></i>
    </button>
    <h1 class="text-lg font-semibold tracking-wide select-none">ACCREDITATION</h1>
  </div>

  <nav class="sidebar p-0 flex-1">
    <ul class="sidebar-menu">
      <li>
        <a class="sidebar-link <?= active('dashboard.php', $current) ?>" href="dashboard.php" <?= $current === 'dashboard.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-table-cells-large inline-block w-5 text-center"></i>
          <span>Dashboard</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('programs.php', $current) ?>" href="programs.php" <?= $current === 'programs.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-table-list inline-block w-5 text-center"></i>
          <span>Programs</span>
        </a>
      </li>

      <li class="has-submenu" data-submenu="accreditation">
        <details class="sidebar-group" <?= in_array($current, ['rates.php', 'instrument.php'], true) ? 'open' : '' ?>>
          <summary class="sidebar-link flex items-center justify-between">
            <span class="inline-flex items-center gap-2">
              <i class="fa-solid fa-certificate inline-block w-5 text-center"></i>
              <span>Accreditation</span>
            </span>
            <i class="fa-solid fa-chevron-down caret" aria-hidden="true"></i>
          </summary>
          <div class="submenu-anim">
            <ul class="submenu ml-8 mt-1">
              <li>
                <a class="sidebar-link <?= active('rates.php', $current) ?>" href="rates.php" <?= $current === 'rates.php' ? 'aria-current="page"' : '' ?>>
                  <span>Rates</span>
                </a>
              </li>
              <li>
                <a class="sidebar-link <?= active('instrument.php', $current) ?>" href="instrument.php" <?= $current === 'instrument.php' ? 'aria-current="page"' : '' ?>>
                  <span>Instrument</span>
                </a>
              </li>
            </ul>
          </div>
        </details>
      </li>

      <li>
        <a class="sidebar-link <?= active('tasks.php', $current) ?>" href="tasks.php" <?= $current === 'tasks.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-clipboard-list inline-block w-5 text-center"></i>
          <span>Tasks</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('documents.php', $current) ?>" href="documents.php" <?= $current === 'documents.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-regular fa-file-lines inline-block w-5 text-center"></i>
          <span>Documents</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('visit.php', $current) ?>" href="visit.php" <?= $current === 'visit.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-route inline-block w-5 text-center"></i>
          <span>Visits</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('facilities.php', $current) ?>" href="facilities.php" <?= $current === 'facilities.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-building inline-block w-5 text-center"></i>
          <span>Facilities</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('users.php', $current) ?>" href="users.php" <?= $current === 'users.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-regular fa-user inline-block w-5 text-center"></i>
          <span>Users</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('archive.php', $current) ?>" href="archive.php" <?= $current === 'archive.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-archive inline-block w-5 text-center"></i>
          <span>Archive</span>
        </a>
      </li>

      <li>
        <a class="sidebar-link <?= active('audit_trail.php', $current) ?>" href="audit_trail.php" <?= $current === 'audit_trail.php' ? 'aria-current="page"' : '' ?>>
          <i class="fa-solid fa-file-shield inline-block w-5 text-center"></i>
          <span>Audit Trail</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="mt-auto border-t p-4 flex items-center gap-3">
    <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
      <i class="fa-regular fa-user"></i>
    </div>
    <div class="text-sm">
      <div class="font-medium"><?= htmlspecialchars($displayName) ?></div>
      <div class="text-gray-500"><?= htmlspecialchars($prettyRole) ?></div>
    </div>
    <a class="ml-auto text-gray-400 hover:text-gray-600" href="logout.php" title="Sign out">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span class="sr-only">Sign out</span>
    </a>
  </div>
</aside>

<script src="../app/js/sidebar.js?v=<?= filemtime(__DIR__.'/../../app/js/sidebar.js') ?>"></script>