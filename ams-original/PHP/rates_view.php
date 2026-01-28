<?php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/db.php';
$current = basename($_SERVER['PHP_SELF']);
function active($page, $current)
{
  return $current === $page ? 'active' : '';
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  $error = "Missing program id.";
}

// Fetch program + accreditation info
$prog = null;
$acc = null;
$err = null;
if (!isset($error)) {
  try {
    // Ensure program_accreditation table exists (safe if already)
    $pdo->exec("CREATE TABLE IF NOT EXISTS program_accreditation (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      program_id INT UNSIGNED NOT NULL,
      level VARCHAR(32) NOT NULL DEFAULT 'Candidate',
      phase VARCHAR(32) DEFAULT NULL,
      status VARCHAR(16) NOT NULL DEFAULT 'active',
      PRIMARY KEY(id),
      UNIQUE KEY uq_prog (program_id),
      CONSTRAINT fk_prog FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    $stmt = $pdo->prepare("SELECT p.*, a.level, a.phase, a.status
                            FROM programs p
                            LEFT JOIN program_accreditation a ON a.program_id=p.id
                            WHERE p.id=:id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
      $prog = $row;
    } else {
      $err = 'Program not found.';
    }
  } catch (Throwable $e) {
    $err = 'Error fetching data.';
  }
}
?>
<!DOCTYPE html>
<html lang=\"en\">

<head>
  <meta charset=\"UTF-8\" />
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
  <title>Program Rate • <?= htmlspecialchars($prog['code'] ?? 'View', ENT_QUOTES) ?></title>
  <script src=\"https://cdn.tailwindcss.com\"></script>
  <link href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css\" rel=\"stylesheet\" />
  <link rel=\"stylesheet\" href=\"../app/css/dashboard.css?v=2\" />
</head>

<body class=\"bg-gray-100 text-gray-800\">
  <div class=\"flex min-h-screen\">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class=\"flex-1 overflow-auto\">
      <header class=\"px-10 py-6 border-b bg-white flex items-center justify-between\">
        <h1 class=\"text-2xl font-semibold\">PROGRAM RATE</h1>
        <a href=\"rates.php\" class=\"btn-light\"><i class=\"fa-solid fa-arrow-left mr-1\"></i> Back to Rates</a>
      </header>

      <section class=\"px-10 py-6\">
        <?php if (isset($error)): ?>
          <div class=\"panel p-4 rounded-lg bg-white border border-red-200 text-red-700\">
            <?= htmlspecialchars($error, ENT_QUOTES) ?>
          </div>
        <?php elseif (isset($err)): ?>
          <div class=\"panel p-4 rounded-lg bg-white border border-red-200 text-red-700\">
            <?= htmlspecialchars($err, ENT_QUOTES) ?>
          </div>
        <?php else: ?>
          <div class=\"panel p-6 rounded-xl bg-white shadow-sm border border-slate-200 max-w-3xl\">
            <div class=\"flex items-start justify-between gap-4\">
              <div>
                <div class=\"text-xl font-semibold\">
                  <?= htmlspecialchars($prog['code'] ?? 'Program', ENT_QUOTES) ?>
                </div>
                <div class=\"text-slate-600 mt-1\">
                  <?= htmlspecialchars($prog['name'] ?? '', ENT_QUOTES) ?>
                </div>
              </div>
              <span class=\"inline-flex items-center text-sm px-3 py-1 rounded-full border\" style=\"border-color: var(--stroke);\">
                Status: <strong class=\"ml-1\"><?= htmlspecialchars($prog['status'] ?? 'active', ENT_QUOTES) ?></strong>
              </span>
            </div>

            <dl class=\"grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6\">
              <div>
                <dt class=\"text-sm text-slate-500\">Level</dt>
                <dd class=\"text-base\"><?= htmlspecialchars($prog['level'] ?? 'Candidate', ENT_QUOTES) ?></dd>
              </div>
              <div>
                <dt class=\"text-sm text-slate-500\">Phase</dt>
                <dd class=\"text-base\"><?= htmlspecialchars($prog['phase'] ?? 'Phase 1', ENT_QUOTES) ?></dd>
              </div>
            </dl>

            <?php if (!empty($prog['description'])): ?>
              <div class=\"mt-6\">
                <div class=\"text-sm text-slate-500\">Description</div>
                <div class=\"mt-1 text-slate-800\"><?= nl2br(htmlspecialchars($prog['description'], ENT_QUOTES)) ?></div>
              </div>
            <?php endif; ?>

            <div class=\"mt-8 flex justify-end gap-3\">
              <a href=\"rates.php\" class=\"btn-light\"><i class=\"fa-solid fa-list mr-1\"></i> All Programs</a>
            </div>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script src=\"../app/js/dashboard.js\"></script>
</body>

</html>