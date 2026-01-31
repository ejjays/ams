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
  <title>Instruments • Accreditation</title>
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

    <main class="flex-1 overflow-auto">
      <!-- Header -->
      <header class="px-10 py-8 border-b bg-white/80 backdrop-blur-md sticky top-0 z-30">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <div class="space-y-1">
            <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3 uppercase">
              <span>INSTRUMENTS</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Evaluation & Assessment Tools</p>
          </div>

          <div class="flex items-center gap-4">
            <button id="instrumentEditToggleBtn"
              class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 transition-all active:scale-95 shadow-sm"
              title="Toggle Edit Mode">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button id="openCreateInstrument"
              class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
              <i class="fa-solid fa-plus text-lg"></i>
              <span>Create Instrument</span>
            </button>
          </div>
        </div>
      </header>

      <section class="px-10 py-10 max-w-5xl mx-auto">
        <div id="instrumentList" class="grid gap-6 sm:grid-cols-1 lg:grid-cols-2">
          <!-- items load here -->
        </div>
      </section>

      <!-- Create/Update Instrument Modal -->
      <div id="instrumentModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[520px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-file-invoice text-lg"></i>
              </div>
              <div>
                <h3 id="instrumentModalTitle" class="text-xl font-black tracking-tight uppercase">Instrument</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Define evaluation tool</p>
              </div>
            </div>
            <button id="instrumentCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <form id="instrumentCreateForm" class="p-8 space-y-6">
            <input type="hidden" id="instrument_id" name="id" />
            
            <div class="space-y-2">
              <label for="instrument_name" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Instrument Name</label>
              <input id="instrument_name" name="name" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                placeholder="e.g., SUC Leveling, Program Performance" required />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="instrumentCreateCancel"
                class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" id="instrumentCreateSubmit"
                class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Instrument</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
  <script src="../app/js/instruments.js?v=<?= filemtime(__DIR__.'/../app/js/instruments.js') ?>"></script>
</body>

</html>