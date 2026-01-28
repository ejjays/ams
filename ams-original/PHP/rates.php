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
  <link rel="stylesheet" href="../app/css/rates.table.css" />
</head>

<body class="bg-slate-50 text-slate-900">
  <div class="flex h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex-1 overflow-auto">
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">PROGRAM RATES</h1>

          <div class="flex items-center gap-3">
            <!-- Search with leading icon -->
            <label for="ratesSearch" class="sr-only">Search programs</label>
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input id="ratesSearch" type="search" placeholder="Search programs, level, phase, status…"
                class="w-80 rounded-lg border border-slate-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder:text-slate-400" />
            </div>
            <!-- Verify button styled like 'Create' -->
            <button id="verifyRatesBtn"
              class="inline-flex items-center gap-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 shadow">
              <i class="fa-solid fa-check"></i>
              <span>Verify Rates</span>
            </button>
          </div>

        </div>
      </header>

      <section class="px-6 py-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full table-sticky">
              <thead class="bg-slate-50 text-slate-700 text-sm">
                <tr>
                  <th class="text-left font-semibold px-4 py-3">Program</th>
                  <th class="text-left font-semibold px-4 py-3">Level</th>
                  <th class="text-left font-semibold px-4 py-3">Phase</th>
                  <th class="text-left font-semibold px-4 py-3">Status</th>
                  <th class="text-right font-semibold px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody id="accredTableBody" class="divide-y divide-slate-100 bg-white">
                <!-- rows injected by JS -->
                <tr>
                  <td colspan="5" class="px-4 py-6 text-center text-slate-500">Loading programs…</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Ratings Modal -->
  <div id="ratingsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40">
    <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl ring-1 ring-slate-200">
      <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
        <h2 id="ratingsModalTitle" class="text-base font-semibold">Ratings</h2>
        <button id="ratingsModalClose" class="text-slate-500 hover:text-slate-700">&times;</button>
      </div>
      <div class="px-5 py-4">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-slate-500 text-sm">Section rates</p>
            <ul id="ratingsModalAreas" class="mt-2 space-y-1 text-slate-700 text-sm"></ul>
          </div>
          <div class="text-right">
            <div class="text-slate-500 text-sm">Total</div>
            <div id="ratingsModalScore" class="text-3xl font-semibold">—</div>
          </div>
        </div>
      </div>
      <div class="px-5 py-3 border-t border-slate-200 flex items-center justify-end gap-2">
        <button id="ratingsConfirm"
          class="rounded-md bg-slate-200 text-slate-500 cursor-not-allowed text-sm font-medium px-4 py-2"
          disabled>Confirm</button>
      </div>
    </div>
  </div>

  <script src="../app/js/dashboard.js"></script>
  <script src="../app/js/rates_table.js?v=1"></script>

  <script src="../app/js/sidebar.js?v=1"></script>
</body>

</html>