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
  <title>Facilities • Accreditation</title>
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
              <span>FACILITIES</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Physical Plant & Asset Management</p>
          </div>

          <div class="flex items-center gap-2">
            <a href="facilities_api.php?action=export" id="facExportBtn" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white border border-slate-200 text-slate-600 text-sm font-bold uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
              <i class="fa-regular fa-file-excel text-green-600"></i>
              <span>Export CSV</span>
            </a>
            <button id="facAddBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
              <i class="fa-solid fa-plus text-lg"></i>
              <span>Add Facility</span>
            </button>
          </div>
        </div>
      </header>

      <section class="px-10 py-10 max-w-7xl mx-auto pb-24">
        <!-- Filter Card -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200/60 p-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="relative group flex-1 max-w-md">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                    <input id="facSearch" type="search" placeholder="Search facilities by name, type, or location..."
                        class="w-full pl-11 pr-4 py-3 bg-slate-100 border-transparent rounded-2xl text-sm font-bold text-slate-700 outline-none focus:bg-white focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600/30 transition-all shadow-inner" />
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="border-b border-slate-100">
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Facility Details</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Category</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Spatial Data</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400">Last Synced</th>
                  <th class="px-8 py-6 text-xs font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                </tr>
              </thead>
              <tbody id="facTableBody" class="divide-y divide-slate-50">
                <!-- items load here -->
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal -->
  <div id="facModal" class="modal hidden">
    <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
    <div class="modal-card w-[560px] border-0 rounded-[2rem] shadow-2xl overflow-hidden p-0">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
            <i class="fa-solid fa-building text-lg"></i>
          </div>
          <div>
            <h3 id="facModalTitle" class="text-xl font-black tracking-tight uppercase">New Facility</h3>
            <p class="text-[10px] font-bold text-white/60 uppercase tracking-widest">Physical plant record</p>
          </div>
        </div>
        <button id="facModalClose" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10 transition-colors">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Modal Body -->
      <form id="facForm" class="p-8 space-y-6">
        <input type="hidden" name="id" id="facId" />
        
        <div class="space-y-2">
          <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Official Name <span class="text-rose-500">*</span></label>
          <input id="facName" name="name" 
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
            placeholder="e.g., University Library, Computer Lab 1" required />
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Asset Type <span class="text-rose-500">*</span></label>
                <input id="facType" name="type" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                    placeholder="e.g., Lab, Building" required />
            </div>
            <div class="space-y-2">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Spatial Location</label>
                <input id="facLocation" name="location" 
                    class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all" 
                    placeholder="e.g., North Campus / 2F" />
            </div>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-black uppercase tracking-widest text-slate-500 ml-1">Technical Notes</label>
          <textarea id="facNotes" name="notes" 
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-slate-700 font-bold outline-none focus:ring-4 focus:ring-blue-600/10 focus:border-blue-600 transition-all resize-none" 
            rows="3" placeholder="Condition, equipment, schedules, etc."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" id="facCancel"
            class="px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all active:scale-95">Cancel</button>
          <button type="submit"
            class="px-8 py-3 rounded-2xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">Save Facility</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const api = 'facilities_api.php';
    const tableBody = document.getElementById('facTableBody');
    const searchInput = document.getElementById('facSearch');
    const modal = document.getElementById('facModal');
    const modalTitle = document.getElementById('facModalTitle');
    const form = document.getElementById('facForm');

    const showToast = (msg, type = 'success') => {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const t = document.createElement('div');
        t.className = `flex items-center gap-3 px-6 py-3.5 rounded-2xl text-white text-xs font-black uppercase tracking-widest shadow-2xl transition-all duration-500 translate-y-[-20px] opacity-0 ${type === 'error' ? 'bg-rose-600 shadow-rose-200' : 'bg-emerald-600 shadow-emerald-200'}`;
        t.style.pointerEvents = 'auto';
        t.innerHTML = `<i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i> <span>${msg}</span>`;

        container.appendChild(t);

        // Trigger Entrance
        setTimeout(() => {
            t.classList.remove('translate-y-[-20px]', 'opacity-0');
        }, 10);

        // Auto Remove
        setTimeout(() => {
            t.classList.add('translate-y-[-20px]', 'opacity-0');
            setTimeout(() => t.remove(), 500);
        }, 3000);
    };

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = String(s == null ? '' : s);
        return d.innerHTML;
    };

    const openModal = (title, data = {}) => {
      modalTitle.textContent = title;
      form.reset();
      document.getElementById('facId').value = data.id || '';
      document.getElementById('facName').value = data.name || '';
      document.getElementById('facType').value = data.type || '';
      document.getElementById('facLocation').value = data.location || '';
      document.getElementById('facNotes').value = data.notes || '';
      modal.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
    };
    const closeModal = () => {
      modal.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    };

    document.getElementById('facModalClose').addEventListener('click', closeModal);
    document.getElementById('facCancel').addEventListener('click', closeModal);
    document.getElementById('facAddBtn').addEventListener('click', () => openModal('New Facility'));
    document.getElementById('facExportBtn').addEventListener('click', () => showToast('Your export has started!', 'success'));

    async function listFacilities(q = '') {
      try {
        tableBody.innerHTML = '<tr><td colspan="5" class="px-8 py-20 text-center"><div class="flex flex-col items-center justify-center space-y-4"><div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div><p class="text-xs font-black uppercase tracking-widest text-slate-400">Scanning facilities...</p></div></td></tr>';
        const resp = await fetch(api + (q ? ('?q=' + encodeURIComponent(q)) : ''));
        const data = await resp.json();
        
        if (!data.ok) throw new Error(data.error || 'Unknown error');
        
        if (!data.items.length) {
          tableBody.innerHTML = `<tr><td colspan="5" class="px-8 py-24 text-center">
              <div class="flex flex-col items-center justify-center space-y-4">
                <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200">
                    <i class="fa-solid fa-building-circle-exclamation text-3xl"></i>
                </div>
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">No assets found</p>
                    <p class="text-xs font-bold text-slate-300 mt-1 uppercase tracking-widest">Try adjusting your filters or add a new facility.</p>
                </div>
              </div>
            </td></tr>`;
          return;
        }

        tableBody.innerHTML = data.items.map(it => `
          <tr class="hover:bg-slate-50/50 transition-all duration-200 group border-b border-slate-50 last:border-0">
            <td class="px-8 py-6">
                <span class="text-[15px] font-black text-slate-900 tracking-tight leading-none block">${esc(it.name)}</span>
                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-1 block">Registered Facility</span>
            </td>
            <td class="px-8 py-6">
                <span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest ring-1 bg-indigo-50 text-indigo-700 ring-indigo-100">${esc(it.type)}</span>
            </td>
            <td class="px-8 py-6">
                <div class="flex items-center gap-2 text-slate-400">
                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                    <span class="text-xs font-black uppercase tracking-[0.15em] whitespace-nowrap">${esc(it.location)}</span>
                </div>
            </td>
            <td class="px-8 py-6">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${it.updated_at ? new Date(it.updated_at).toLocaleDateString() : 'Never'}</span>
            </td>
            <td class="px-8 py-6 text-right">
              <button class="w-11 h-11 inline-flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-100/50 transition-all active:scale-95 group-hover:border-blue-200" 
                onclick='editFacility(${it.id})' title="Edit Record">
                <i class="fa-solid fa-pen-nib text-sm"></i>
              </button>
            </td>
          </tr>
        `).join('');
        window.__facilitiesCache = data.items;
      } catch (err) {
        tableBody.innerHTML = `<tr><td colspan="5" class="px-8 py-20 text-center text-rose-500 font-black uppercase tracking-widest text-xs">Failure: ${err.message}</td></tr>`;
      }
    }

    window.editFacility = (id) => {
      const it = (window.__facilitiesCache || []).find(x => x.id == id);
      if (!it) return;
      openModal('Edit Facility', it);
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = {
        id: form.id.value || undefined,
        name: form.name.value.trim(),
        type: form.type.value.trim(),
        location: form.location.value.trim(),
        notes: form.notes.value.trim(),
      };
      const isEdit = !!payload.id;
      const r = await fetch(api + (isEdit ? ('?id=' + payload.id) : ''), {
        method: isEdit ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await r.json();
      if (data.ok) {
        showToast(isEdit ? 'Facility updated successfully!' : 'Facility added successfully!');
        closeModal();
        listFacilities(searchInput.value.trim());
      } else {
        showToast(data.error || 'Save failed', 'error');
      }
    });

    let debounceTimer;
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => listFacilities(searchInput.value.trim()), 250);
    });

    listFacilities();
  </script>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] flex flex-col gap-3 items-center pointer-events-none"></div>
</body>

</html>