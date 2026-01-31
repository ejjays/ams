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
  <title>Rates • Accreditation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=<?= filemtime(__DIR__.'/../app/css/dashboard.css') ?>" />
  <style>
    body { font-family: 'Inter', sans-serif; }
  </style>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex-1 overflow-auto">
      <header class="px-10 py-8 border-b bg-white/80 backdrop-blur-md sticky top-0 z-30">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <div class="space-y-1">
            <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
              <span>PROGRAM RATES</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Accreditation Compliance & Ratings</p>
          </div>

          <div class="flex items-center gap-4">
            <div class="relative group">
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
              <input id="ratesSearch" type="search" placeholder="Search programs, level, status..."
                class="pl-11 pr-4 py-3 w-80 rounded-2xl bg-slate-100 border-transparent outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all font-bold text-sm text-slate-700 placeholder:text-slate-400 shadow-inner" />
            </div>
            <button id="verifyRatesBtn"
              class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
              <i class="fa-solid fa-check-double text-lg"></i>
              <span>Verify Rates</span>
            </button>
          </div>
        </div>
      </header>

      <section class="px-10 py-10 max-w-7xl mx-auto pb-24">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-100">
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Program Details</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Accreditation Level</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Current Phase</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Compliance Status</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                </tr>
              </thead>
              <tbody id="accredTableBody" class="divide-y divide-slate-50">
                <tr>
                  <td colspan="5" class="px-8 py-20 text-center">
                    <div class="flex flex-col items-center justify-center space-y-4">
                      <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
                      <p class="text-xs font-black uppercase tracking-widest text-slate-400">Loading programs...</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Ratings Modal -->
  <div id="ratingsModal" class="modal hidden">
    <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
    <div class="modal-card w-[600px] border-0 rounded-[2.5rem] shadow-2xl overflow-hidden p-0">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md">
            <i class="fa-solid fa-chart-line text-xl"></i>
          </div>
          <div>
            <h3 id="ratingsModalTitle" class="text-xl font-black tracking-tight uppercase">Ratings Details</h3>
            <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Program compliance breakdown</p>
          </div>
        </div>
        <button id="ratingsModalClose" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-8 space-y-8">
        <div class="flex items-center justify-between bg-slate-50 p-6 rounded-3xl border border-slate-100">
          <div>
            <p class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1">Total Compliance Score</p>
            <div id="ratingsModalScore" class="text-5xl font-black text-slate-900 tracking-tighter">—</div>
          </div>
          <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center text-blue-600 border border-slate-100">
            <i class="fa-solid fa-award text-3xl"></i>
          </div>
        </div>

        <div class="space-y-4">
            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Sectionized Performance</label>
            <ul id="ratingsModalAreas" class="grid grid-cols-1 gap-3">
                <!-- Areas Injected here -->
            </ul>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <button id="ratingsModalCloseBtn" data-close="true" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Close</button>
            <button id="ratingsConfirm" class="px-8 py-3 rounded-2xl bg-slate-200 text-slate-400 text-xs font-black uppercase tracking-widest cursor-not-allowed transition-all" disabled>Confirm Evaluation</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../app/js/rates_table.js?v=<?= filemtime(__DIR__.'/../app/js/rates_table.js') ?>"></script>
</body>

</html>