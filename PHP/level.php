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
  <title>Levels • Accreditation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=<?= filemtime(__DIR__.'/../app/css/dashboard.css') ?>" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    .tag-mode [data-action="open-tag-modal"] {
        animation: pulse-blue 2s infinite;
    }
    @keyframes pulse-blue {
        0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
        100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
    }
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
              <span>LEVELS</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Hierarchy & Program Tagging</p>
          </div>

          <div class="flex items-center gap-3">
            <button id="levelEditToggleBtn"
              class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 transition-all active:scale-95 shadow-sm"
              title="Toggle Edit Mode">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button id="openTagProgramLevel"
              class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white border border-slate-200 text-slate-600 text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
              <i class="fa-solid fa-tag text-lg"></i>
              <span>Tag Program</span>
            </button>
            <button id="openCreateLevel"
              class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
              <i class="fa-solid fa-plus text-lg"></i>
              <span>Create Level</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Back link -->
      <div class="px-10 pt-6">
        <a href="#" onclick="history.back(); return false;"
          class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-700 group transition-all">
          <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
          <span>Return to Instruments</span>
        </a>
      </div>

      <section class="px-10 py-8 max-w-6xl mx-auto">
        <div id="levelList" class="flex flex-col gap-5">
            <!-- Items load here -->
        </div>
      </section>

      <!-- Create/Update Level Modal -->
      <div id="levelModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[520px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-layer-group text-lg"></i>
              </div>
              <div>
                <h3 id="levelModalTitle" class="text-xl font-black tracking-tight uppercase">Level</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Accreditation stage</p>
              </div>
            </div>
            <button id="levelCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <form id="levelCreateForm" class="p-8 space-y-6">
            <input type="hidden" id="level_id" name="id" />
            
            <div class="space-y-2">
              <label for="level_name" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Level Name</label>
              <input id="level_name" name="name" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                placeholder="e.g., Level III, Candidate Status" required />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="levelCreateCancel"
                class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" id="levelCreateSubmit"
                class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Level</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Tag Program Modal -->
      <div id="tagProgramLevelModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[480px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-tag text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-black tracking-tight uppercase">Tag Program</h3>
                <p id="tagProgramLevelContext" class="text-[10px] font-bold text-white/60 uppercase tracking-widest"></p>
              </div>
            </div>
            <button id="tagProgramLevelModalClose" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <form id="tagProgramLevelModalForm" class="p-8 space-y-6">
            <div class="space-y-2">
              <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1" for="tagProgramLevelModalProgram">Select Program</label>
              <div class="relative">
                <select id="tagProgramLevelModalProgram" required 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 transition-all appearance-none cursor-pointer shadow-inner">
                </select>
                <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
              </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" id="tagProgramLevelModalCancel" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
                <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Link Program</button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>
  <script src="../app/js/levels.js?v=<?= filemtime(__DIR__.'/../app/js/levels.js') ?>"></script>

  <script>
    (function() {
      const btn = document.getElementById('openTagProgramLevel');
      if (btn) {
        btn.addEventListener('click', function() {
          document.body.classList.toggle('tag-mode');
          btn.classList.toggle('bg-blue-600', document.body.classList.contains('tag-mode'));
          btn.classList.toggle('text-white', document.body.classList.contains('tag-mode'));
          btn.classList.toggle('border-blue-600', document.body.classList.contains('tag-mode'));
        });
      }
    })();
  </script>

</body>

</html>