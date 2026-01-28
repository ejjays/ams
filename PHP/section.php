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
  <title>Section • Rates</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=1" />
  <link rel="stylesheet" href="../app/css/theme.cards.css?v=1" />


  <style>
    /* Section page: make cards full-width vertical list */
    #sectionList {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      overflow: visible;
    }

    #sectionList .program-chip {
      width: 100%;
      min-width: 0;
    }

    #sectionList .program-chip span {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  </style>


  <script>
    // Keep level_id stable even if the URL drops it after async actions.
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
      <!-- Header: title + Verify Rates (ONLY) -->
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">SECTION</h1>

          <div class="flex items-center gap-3">
            <button id="openCreateSection"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-plus mr-1"></i> Create Section
            </button>
            <button id="openTagProgramSection"
              class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-tag mr-1"></i> Tag Program
            </button>
            <button id="sectionEditToggleBtn"
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
        <div id="sectionList" class="chip-row">
          <!-- items load here -->
        </div>
      </section>

      <!-- Create/Update Section Modal -->
      <div id="sectionModal" class="modal hidden">
        <div class="modal-backdrop" data-close="true"></div>
        <div class="modal-card">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Section</h3>
            <button id="sectionCloseX" class="text-gray-500 hover:text-gray-700" title="Close">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <form id="sectionCreateForm" class="space-y-3">
            <input type="hidden" id="section_id" name="id" />
            <div>
              <label for="section_name" class="block text-sm font-medium text-slate-700 mb-1">Section</label>
              <input id="section_name" name="name" class="w-full border rounded-md px-3 py-2" placeholder="Section" required />
            </div>
            <div class="flex justify-between pt-2">
              <button type="button" id="sectionCreateCancel"
                class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
              <button type="submit" id="instrumentCreateSubmit"
                class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
            </div>
          </form>
        </div>
      </div>




      <!-- Create Parameter Modal -->
      <div id="parameterModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="true"></div>
        <div class="relative mx-auto mt-24 w-full max-w-lg">
          <div class="bg-white rounded-lg shadow">
            <div class="px-5 py-3 border-b flex items-center justify-between">
              <h3 class="text-lg font-semibold">Create Parameter</h3>
              <button type="button" id="paramCloseX" class="text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <form id="paramForm" class="p-5 space-y-3">
              <input type="hidden" id="param_section_id" />
              <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input id="param_name" type="text" class="w-full rounded border px-3 py-2" placeholder="Parameter A — Statement of VMGO" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Description <span class="text-gray-400">(optional)</span></label>
                <textarea id="param_description" class="w-full rounded border px-3 py-2" rows="3" placeholder="Short description…"></textarea>
              </div>
              <div class="mt-5 flex justify-end gap-2 border-t pt-3">
                <button type="button" id="paramCancel" class="px-4 py-2 rounded border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Parameter Label Modal -->
      <div id="labelModal" class="fixed inset-0 bg-black/40 z-50 hidden">
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h3 class="text-lg font-semibold">Add Parameter Label</h3>
              <button id="labelCloseX" class="text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <form id="labelForm" class="p-4 space-y-3">
              <input type="hidden" id="label_param_id" />
              <div>
                <label for="label_name" class="block text-sm font-medium text-slate-700 mb-1">Label</label>
                <input id="label_name" class="w-full rounded border px-3 py-2" placeholder="e.g., System – Inputs and Processes" required />
              </div>
              <div class="mt-5 flex justify-end gap-2 border-t pt-3">
                <button type="button" id="labelCancel" class="px-4 py-2 rounded border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>


      <!-- Indicator Label Modal -->
      <div id="indicatorModal" class="fixed inset-0 bg-black/40 z-50 hidden">
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h3 class="text-lg font-semibold">Add Indicator Label</h3>
              <button id="indicatorCloseX" class="text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <form id="indicatorForm" class="p-4 space-y-3">
              <input type="hidden" id="indicator_label_id" />
              <div class="grid grid-cols-12 gap-3">
                <div class="col-span-3">
                  <label for="indicator_code" class="block text-sm font-medium text-slate-700 mb-1">Code (optional)</label>
                  <input id="indicator_code" class="w-full rounded border px-3 py-2" placeholder="e.g., S.1" />
                </div>
                <div class="col-span-9">
                  <label for="indicator_title" class="block text-sm font-medium text-slate-700 mb-1">Indicator</label>
                  <textarea id="indicator_title" class="w-full rounded border px-3 py-2" rows="3" placeholder="Indicator text…" required></textarea>
                </div>
              </div>
              <div>
                <label for="indicator_evidence" class="block text-sm font-medium text-slate-700 mb-1">Evidence to attach (optional)</label>
                <textarea id="indicator_evidence" class="w-full rounded border px-3 py-2" rows="2" placeholder="Notes / sample evidence…"></textarea>
              </div>
              <div class="mt-5 flex justify-end gap-2 border-t pt-3">
                <button type="button" id="indicatorCancel" class="px-4 py-2 rounded border">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Tag Program Modal -->
      <div id="tagProgramSectionModal" class="fixed inset-0 bg-black/40 z-50 hidden">
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between border-b px-4 py-3">
              <h3 class="text-lg font-semibold">Tag Program</h3>
              <button type="button" id="tagProgramSectionModalClose" class="text-slate-500 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <form id="tagProgramSectionModalForm" class="px-4 py-4 space-y-3">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="tagProgramSectionModalProgram">Program</label>
                <select id="tagProgramSectionModalProgram" class="w-full border rounded-md px-3 py-2"></select>
              </div>
              <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="tagProgramSectionModalCancel" class="px-4 py-2 rounded-lg bg-gray-400 hover:bg-gray-500 text-white">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </main>
  </div>
  <script src="../app/js/sections.js?v=11"></script>
</body>

</html>