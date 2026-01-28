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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../app/css/dashboard.css?v=3" />
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="flex min-h-screen">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <div class="flex items-center gap-3">
            <h1 class="text-xl md:text-2xl font-semibold">FACILITIES</h1>
          </div>
          <div class="flex items-center gap-2">
            <a href="facilities_api.php?action=export" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-800">
              <i class="fa-regular fa-file-excel mr-2"></i>Export CSV
            </a>
            <button id="facAddBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-medium shadow">
              <i class="fa-solid fa-plus"></i>Add Facility
            </button>
          </div>
        </div>
      </header>

      <section class="p-10">
        <div class="bg-white rounded-xl border p-5">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <input id="facSearch" type="search" placeholder="Search facilities..." class="px-3 py-2 border rounded-lg w-72" />
            </div>
          </div>

          <div class="mt-6 grid grid-cols-12 gap-2 px-3 py-2 text-xs uppercase tracking-wide text-gray-500">
            <div class="col-span-3 font-semibold md:text-sm">Name</div>
            <div class="col-span-2 font-semibold md:text-sm">Type</div>
            <div class="col-span-3 font-semibold md:text-sm">Location</div>
            <div class="col-span-2 font-semibold md:text-sm">Updated</div>
            <div class="col-span-2 font-semibold md:text-sm text-right">Actions</div>
          </div>
          <div id="facTableBody" class="mt-2"></div>
        </div>
      </section>
    </main>
  </div>

  <div id="facModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
    <div class="bg-white rounded-xl w-[92vw] max-w-xl p-6 shadow-lg">
      <div class="flex items-center justify-between mb-4">
        <h2 id="facModalTitle" class="text-lg font-semibold">New Facility</h2>
        <button id="facModalClose" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form id="facForm" class="space-y-4">
        <input type="hidden" name="id" id="facId" />
        <div>
          <label class="block text-sm mb-1">Name <span class="text-red-600">*</span></label>
          <input id="facName" name="name" class="w-full border rounded-lg px-3 py-2" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Type <span class="text-red-600">*</span></label>
          <input id="facType" name="type" class="w-full border rounded-lg px-3 py-2" placeholder="e.g., Campus, Building, Room, Lab" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Location</label>
          <input id="facLocation" name="location" class="w-full border rounded-lg px-3 py-2" placeholder="e.g., North Campus / 2F" />
        </div>
        <div>
          <label class="block text-sm mb-1">Notes</label>
          <textarea id="facNotes" name="notes" class="w-full border rounded-lg px-3 py-2" rows="3" placeholder="Condition, equipment, schedules, etc."></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" id="facCancel" class="px-4 py-2 rounded-lg border">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white">Save</button>
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

    const openModal = (title, data = {}) => {
      modalTitle.textContent = title;
      form.reset();
      document.getElementById('facId').value = data.id || '';
      document.getElementById('facName').value = data.name || '';
      document.getElementById('facType').value = data.type || '';
      document.getElementById('facLocation').value = data.location || '';
      document.getElementById('facNotes').value = data.notes || '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    };
    const closeModal = () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    };

    document.getElementById('facModalClose').addEventListener('click', closeModal);
    document.getElementById('facCancel').addEventListener('click', closeModal);
    document.getElementById('facAddBtn').addEventListener('click', () => openModal('New Facility'));

    async function listFacilities(q = '') {
      try {
        tableBody.innerHTML = '<div class="p-6 text-center text-gray-500">Loading…</div>';
        const resp = await fetch(api + (q ? ('?q=' + encodeURIComponent(q)) : ''));
        if (!resp.ok) {
          const txt = await resp.text();
          tableBody.innerHTML = '<div class="p-6 text-red-600">Load failed: ' + txt + '</div>';
          return;
        }
        let data;
        try {
          data = await resp.json();
        } catch (e) {
          const txt = await resp.text();
          tableBody.innerHTML = '<div class="p-6 text-red-600">Invalid JSON: ' + txt + '</div>';
          return;
        }
        if (!data.ok) {
          tableBody.innerHTML = '<div class="p-6 text-red-600">Error: ' + (data.error || 'Unknown') + '</div>';
          return;
        }
        if (!data.items.length) {
          tableBody.innerHTML = `<div class="p-10 text-center text-gray-500">
              <i class="fa-solid fa-building text-3xl mb-3"></i>
              <div class="font-medium mb-1">No facilities found</div>
              <div class="mb-4">Click <strong>Add Facility</strong> to create your first facility.</div>
            </div>`;
          return;
        }
        const rows = data.items.map(it => `
          <div class="grid grid-cols-12 gap-2 items-center px-3 py-3 border-b">
            <div class="col-span-3 font-medium">${(it.name||'')}</div>
            <div class="col-span-2">${(it.type||'')}</div>
            <div class="col-span-3">${(it.location||'')}</div>
            <div class="col-span-2 text-sm text-gray-500">${it.updated_at ? new Date(it.updated_at).toLocaleString() : ''}</div>
            <div class="col-span-2 text-right space-x-5">
              <button class="bg-transparent p-0 m-0 border-0 text-blue-600 hover:underline focus:underline" onclick='editFacility(${it.id})'>Edit</button>
            </div>
          </div>
        `).join('');
        tableBody.innerHTML = rows;
        window.__facilitiesCache = data.items;
      } catch (err) {
        tableBody.innerHTML = '<div class="p-6 text-red-600">Request error: ' + (err.message || err) + '</div>';
      }
    }

    window.editFacility = (id) => {
      const it = (window.__facilitiesCache || []).find(x => x.id == id);
      if (!it) return;
      openModal('Edit Facility', it);
    };

    // Delete function removed

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
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      const data = await r.json();
      if (data.ok) {
        closeModal();
        listFacilities(document.getElementById('facSearch').value.trim());
      } else {
        alert(data.error || 'Save failed');
      }
    });

    let debounceTimer;
    document.getElementById('facSearch').addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => listFacilities(document.getElementById('facSearch').value.trim()), 250);
    });

    listFacilities();
  </script>
</body>

</html>