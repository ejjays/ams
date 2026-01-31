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
  <?php $__level_id = intval($_GET["level_id"] ?? 0); ?>
  <meta name="level-id" content="<?php echo $__level_id; ?>">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sections • Accreditation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=<?= filemtime(__DIR__.'/../app/css/dashboard.css') ?>" />
  <style>
    body { font-family: 'Inter', sans-serif; }
    
    /* Modern details/summary styling */
    details > summary::-webkit-details-marker { display: none; }
    details > summary { list-style: none; }
    
    .section-card.is-open {
        border-color: rgba(37, 99, 235, 0.3);
        box-shadow: 0 10px 30px -10px rgba(37, 99, 235, 0.1);
    }
  </style>

  <script>
    (function() {
      try {
        var meta = document.querySelector('meta[name="level-id"]');
        var metaLid = meta ? meta.content : "";
        var storeKey = "level_id_section";
        var stored = localStorage.getItem(storeKey) || "";
        var q = new URLSearchParams(location.search);
        var urlLid = q.get("level_id") || "";

        var lid = urlLid || metaLid || stored || "";
        if (lid) {
          if (stored !== String(lid)) localStorage.setItem(storeKey, String(lid));
          if (!urlLid) {
            q.set("level_id", String(lid));
            var next = location.pathname + "?" + q.toString();
            if (next !== location.pathname + location.search) {
              location.replace(next);
            }
          }
        }
      } catch (e) {}
    })();
  </script>
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
              <span>SECTIONS</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Parameter & Indicator Structure</p>
          </div>

          <div class="flex items-center gap-3">
            <button id="sectionEditToggleBtn"
              class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 transition-all active:scale-95 shadow-sm"
              title="Toggle Edit Mode">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button id="openCreateSection"
              class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
              <i class="fa-solid fa-plus text-lg"></i>
              <span>Create Section</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Back link -->
      <div class="px-10 pt-6">
        <a href="#" onclick="history.back(); return false;"
          class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-700 group transition-all">
          <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
          <span>Return to Levels</span>
        </a>
      </div>

      <section class="px-10 py-8 max-w-6xl mx-auto pb-24">
        <div id="sectionList" class="flex flex-col gap-6">
          <!-- Items injected here -->
        </div>
      </section>

      <!-- Create/Update Section Modal -->
      <div id="sectionModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[520px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-folder-tree text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-black tracking-tight uppercase">Section</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Organize parameters</p>
              </div>
            </div>
            <button id="sectionCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form id="sectionCreateForm" class="p-8 space-y-6">
            <input type="hidden" id="section_id" name="id" />
            <div class="space-y-2">
              <label for="section_name" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Section Name</label>
              <input id="section_name" name="name" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                placeholder="e.g., Section I: VMGO" required />
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="sectionCreateCancel"
                class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Section</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Parameter Modal -->
      <div id="parameterModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[520px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-list-check text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-black tracking-tight uppercase">Parameter</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Define evaluation criteria</p>
              </div>
            </div>
            <button id="paramCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <form id="paramForm" class="p-8 space-y-6">
            <input type="hidden" id="param_section_id" />
            <div class="space-y-2">
              <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Parameter Title</label>
              <input id="param_name" type="text" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                placeholder="e.g., Parameter A — Statement of VMGO" required />
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Description <span class="text-slate-300 font-bold lowercase italic">(optional)</span></label>
                <textarea id="param_description" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all resize-none" 
                    rows="3" placeholder="Additional details..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="paramCancel" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Parameter</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Label Modal -->
      <div id="labelModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[520px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-tag text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-black tracking-tight uppercase">Parameter Label</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Categorize indicators</p>
              </div>
            </div>
            <button id="labelCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <form id="labelForm" class="p-8 space-y-6">
            <input type="hidden" id="label_param_id" />
            <div class="space-y-2">
              <label for="label_name" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Label Name</label>
              <input id="label_name" 
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                placeholder="e.g., System – Inputs and Processes" required />
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="labelCancel" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Label</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Indicator Modal -->
      <div id="indicatorModal" class="modal hidden">
        <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
        <div class="modal-card w-[640px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
          <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                <i class="fa-solid fa-circle-check text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-black tracking-tight uppercase">Indicator</h3>
                <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Specific evaluation point</p>
              </div>
            </div>
            <button id="indicatorCloseX" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <form id="indicatorForm" class="p-8 space-y-6">
            <input type="hidden" id="indicator_label_id" />
            <div class="grid grid-cols-12 gap-6">
              <div class="col-span-4 space-y-2">
                <label for="indicator_code" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Code <span class="text-slate-300 lowercase italic">(optional)</span></label>
                <input id="indicator_code" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 transition-all shadow-inner" placeholder="e.g., S.1" />
              </div>
              <div class="col-span-8 space-y-2">
                <label for="indicator_title" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Indicator Statement</label>
                <textarea id="indicator_title" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 transition-all resize-none shadow-inner" rows="3" placeholder="The VMGO are clearly stated..." required></textarea>
              </div>
            </div>
            <div class="space-y-2">
              <label for="indicator_evidence" class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Suggested Evidence <span class="text-slate-300 lowercase italic">(optional)</span></label>
              <textarea id="indicator_evidence" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 transition-all resize-none shadow-inner" rows="2" placeholder="Notes on sample evidence..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
              <button type="button" id="indicatorCancel" class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
              <button type="submit" class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Indicator</button>
            </div>
          </form>
        </div>
      </div>

    </main>
  </div>
  <script src="../app/js/sections.js?v=<?= filemtime(__DIR__.'/../app/js/sections.js') ?>"></script>
</body>

</html>