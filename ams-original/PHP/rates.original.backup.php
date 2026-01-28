<?php
require __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
function active($page, $current)
{
  return $current === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rates • Programs</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
      <!-- Header: title + Verify Rates (ONLY) -->
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">PROGRAM RATES</h1>
          <button id="verifyRatesBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
  <!-- check-badge icon -->
  <span class="label">Verify Rates</span>
</button>

        </div>
      </header>

      <!-- Tabs row kept below header -->
      <div class="py-4">
        <div class="tabs">

        </div>

        <!-- Card grid -->
        
        <!-- Ratings Modal -->
        <div id="ratingsModal" class="hidden fixed inset-0 z-50">
          <div class="absolute inset-0 bg-black/50"></div>
          <div class="relative mx-auto my-10 w-[720px] max-w-[90%] rounded-xl bg-white shadow-2xl border border-slate-200">
            <header class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
              <h3 class="text-lg font-semibold">Ratings</h3>
              <button id="ratingsClose" class="text-slate-500 hover:text-slate-700" aria-label="Close">
                ×
              </button>
            </header>
            <section class="px-8 py-6">
              <div class="flex items-start justify-between gap-6">
                <div>
                  <div id="ratingsModalTitle" class="text-base font-medium text-slate-800">—</div>
                  <div class="text-sm text-slate-500 mt-1">Section rates</div>
                </div>
                <div id="ratingsModalScore" class="text-2xl font-semibold text-slate-800">0.0</div>
              </div>
              <ul id="ratingsModalAreas" class="mt-4 space-y-2 text-slate-700"></ul>
              <div class="mt-8 flex justify-end">
                <button id="ratingsConfirm" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-500 cursor-not-allowed" disabled>Confirm</button>
              </div>
            </section>
          </div>
        </div>

        <section class="px-6 pb-6">
  <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-700">
        <tr>
          <th class="text-left font-semibold px-4 py-3">Code</th>
          <th class="text-left font-semibold px-4 py-3">Program</th>
          <th class="text-left font-semibold px-4 py-3">Level</th>
          <th class="text-left font-semibold px-4 py-3">Verify</th>
          <th class="text-right font-semibold px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="accredTableBody" class="divide-y divide-slate-100">
        <!-- rows injected by JS -->
      </tbody>
    </table>
  </div>
</section>
    </main>
  </div>

  <script src="../app/js/dashboard.js"></script>
  <script src="../app/js/accreditation.js?v=12"></script>
</body>

</html>