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
  <title>Level • Rates</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=1" />

  <style>
    /* ... (ibang rules sa taas) ... */

    .level-card {
      /* TINANGGAL: min-height: 220px; */
      /* Ang .panel class mula sa JS na ang magbibigay ng padding (px-4 py-1.5) */
      display: flex;
      flex-direction: column;
      width: 100%;
      /* Siguraduhin na umookupa ng buong lapad */
    }

    /* ... (ibang rules) ... */

    /* TINANGGAL: Lahat ng margin-top rules para sa [data-chips-for] */
    /* Dahil ang chips ay nasa loob na ng .level-head */

    .chips-row {
      /* Ito ay hindi na ginagamit sa levels.js */
    }

    /* Ensure consistent height for the header row */
    #levelList .level-head {
      min-height: 44px;
      /* */
      width: 100%;
    }

    /* ... (ibang rules sa baba) ... */
  </style>

</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
      <!-- Header: title + Verify Rates (ONLY) -->
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">LEVEL</h1>

          <div class="flex items-center gap-3">
            <button id="openCreateLevel"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-plus mr-1"></i> Create Level
            </button>
            <button id="openTagProgramLevel"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-tag mr-1"></i> Tag Program
            </button>
            <button id="levelEditToggleBtn"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow"
              title="Toggle edit mode">
              <i class="fa-solid fa-pen"></i>
            </button>
          </div>
        </div>
      </header>
      <!-- Back link below top bar -->
      <div class="px-10 pt-4">
        <a href="#" onclick="history.back(); return false;"
          class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 hover:underline">
          <i class="fa-solid fa-arrow-left"></i>
          <span>Back</span>
        </a>
      </div>

      <section class="px-6 py-6">
        <div id="levelList" class="flex flex-col gap-4">
        </div>
      </section>

      <!-- Create/Update Level Modal -->
      <div id="levelModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Level</h3>
            <button id="levelCloseX" class="text-gray-500 hover:text-gray-700" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <form id="levelCreateForm" class="space-y-3">
            <input type="hidden" id="level_id" name="id" />
            <div>
              <label for="level_name" class="block text-sm font-medium text-slate-700 mb-1">Level</label>
              <input id="level_name" name="name" class="w-full border rounded-md px-3 py-2" placeholder="Level" required />
            </div>
            <div class="flex justify-between pt-2">
              <button type="button" id="levelCreateCancel"
                class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
              <button type="submit" id="levelCreateSubmit"
                class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
            </div>
          </form>
        </div>
      </div>



      <!-- Tag Program Modal -->
      <div id="tagProgramLevelModal" class="fixed inset-0 bg-black/40 z-50 hidden">
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h3 class="text-lg font-semibold">Tag Program</h3>
              <button type="button" id="tagProgramLevelModalClose" class="text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <form id="tagProgramLevelModalForm" class="px-4 py-4 space-y-3">
              <div id="tagProgramLevelContext" class="text-xs text-slate-500 mb-2"></div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="tagProgramLevelModalProgram">Program</label>
                <select id="tagProgramLevelModalProgram" required class="w-full border rounded-md px-3 py-2"></select>
              </div>

              <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="tagProgramLevelModalCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </main>
  </div>
  <script src="../app/js/levels.js?v=5"></script>

  <script>
    // Toggle tag-mode: show/hide per-card tag icons only when top bar "Tag Program" is pressed
    (function() {
      const btn = document.getElementById('openTagProgramLevel');
      if (btn) {
        btn.addEventListener('click', function() {
          document.body.classList.toggle('tag-mode');
        });
      }
    })();
  </script>

</body>

</html>