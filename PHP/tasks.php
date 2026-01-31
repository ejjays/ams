<?php
require_once __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tasks • Accreditation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="../app/css/dashboard.css?v=<?= filemtime(__DIR__.'/../app/css/dashboard.css') ?>">
  <style>
    body { font-family: 'Inter', sans-serif; }
    details > summary::-webkit-details-marker { display: none; }
    details > summary { list-style: none; }
    .tree-section { transition: all 0.3s ease; }
    .tree-section:hover { border-color: rgba(37, 99, 235, 0.3); }
  </style>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="min-h-screen flex">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="flex-1 overflow-auto">
      <!-- Header -->
      <header class="px-10 py-8 border-b bg-white/80 backdrop-blur-md sticky top-0 z-30">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <div class="space-y-1">
            <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3 uppercase">
              <span>TASKS</span>
              <span class="w-2 h-2 rounded-full bg-blue-600 shadow-lg shadow-blue-200"></span>
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] ml-0.5">Progress Management</p>
          </div>

          <div class="flex items-center gap-4">
            <button id="assignCoordBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white border border-slate-200 text-slate-600 text-sm font-black uppercase tracking-widest hover:bg-slate-50 transition-all active:scale-95 shadow-sm">
              <i class="fa-solid fa-user-plus text-blue-600"></i>
              <span>Coordinators</span>
            </button>
            <div class="relative">
              <button id="programBtn" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white text-sm font-black uppercase tracking-widest shadow-xl shadow-blue-200 transition-all active:scale-95">
                <span id="programBtnLabel">Choose program</span>
                <i class="fa fa-chevron-down text-xs opacity-80"></i>
              </button>
              <div id="programMenu" class="absolute right-0 mt-3 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 z-50 hidden overflow-hidden" role="listbox"></div>
            </div>
          </div>
        </div>
      </header>

      <section id="content" class="px-10 py-10 max-w-6xl mx-auto pb-24">
        <!-- Main Loader -->
        <div id="taskLoader" class="flex flex-col items-center justify-center py-20 gap-4">
          <div class="w-12 h-12 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
          <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Synchronizing system data...</p>
        </div>

        <div id="realEmptyState" class="hidden flex flex-col items-center justify-center py-24 px-10 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm">
          <div class="w-20 h-20 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200 mb-6"><i class="fa-solid fa-layer-group text-4xl"></i></div>
          <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Select a Program</h3>
          <p class="text-slate-400 text-sm font-bold uppercase tracking-widest text-center max-w-xs">Please pick a program from the dropdown above.</p>
        </div>

        <div id="mirrorWrap" class="hidden space-y-8">
          <!-- Program Details -->
          <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200/60 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm"><i class="fa-solid fa-graduation-cap text-2xl"></i></div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-1">Active Program</span>
                    <h2 id="activeProgram" class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase"></h2>
                </div>
            </div>
            <div id="activeLevel" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest ring-1 ring-emerald-100"></div>
          </div>

          <!-- Directives Table -->
          <div id="adminTasksCard" class="hidden bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden">
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Directives & Assignments</span>
                <span id="taskCount" class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-lg">0 TASKS</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-50">
                            <th class="px-8 py-4">Description</th>
                            <th class="px-8 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assignRows" class="divide-y divide-slate-50"></tbody>
                </table>
            </div>
          </div>
          
          <div id="mirrorBody" class="space-y-6"></div>
        </div>
      </section>
    </main>
  </div>

  <script>
    (function() {
      // --- Core Helpers ---
      const qs = (s, el = document) => el.querySelector(s);
      const qsa = (s, el = document) => Array.from(el.querySelectorAll(s));
      const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = String(s == null ? '' : s);
        return d.innerHTML;
      };
      const spinner = () => `<svg aria-hidden="true" class="animate-spin text-blue-600" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" fill="none"></path></svg>`;
      
      const loader = qs('#taskLoader'), emptyState = qs('#realEmptyState'), mirrorWrap = qs('#mirrorWrap'), mirrorBody = qs('#mirrorBody');
      const programBtn = qs('#programBtn'), programMenu = qs('#programMenu');

      async function loadIndicatorFiles(indicatorId) {
        const listEl = document.querySelector(`.files-list[data-id="${indicatorId}"]`);
        if (!listEl) return;
        try {
          const res = await fetch(`indicator_documents_api.php?action=list&indicator_id=${indicatorId}`);
          const j = await res.json();
          if (j.ok && j.data && j.data.length > 0) {
            listEl.innerHTML = j.data.map(d => `<a class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-100 text-[10px] font-bold text-blue-600 uppercase tracking-widest hover:border-blue-200 transition-all shadow-sm" href="../uploads/documents/${d.stored_name}" target="_blank"><i class="fa-solid fa-file-pdf"></i><span>${esc(d.title || d.original_name)}</span></a>`).join('');
          } else { listEl.innerHTML = '<span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic">No files attached</span>'; }
        } catch (err) { listEl.innerHTML = 'Error'; }
      }

      function renderTree(sections) {
        if (!sections || sections.length === 0) {
          mirrorBody.innerHTML = '<div class="p-20 text-center bg-white rounded-[2.5rem] border border-slate-200/60 text-slate-400 font-black uppercase text-xs tracking-widest italic">No accreditation sections found.</div>';
          return;
        }
        mirrorBody.innerHTML = sections.map(sec => `
          <section class="tree-section flex flex-col bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm overflow-hidden mb-8">
            <div class="p-8 bg-white border-b border-slate-50 flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fa-solid fa-folder-tree text-2xl"></i></div>
                <div><span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-1">System Section</span><h3 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase">${esc(sec.name)}</h3></div>
            </div>
            <div class="p-8 bg-slate-50/30">
                ${(sec.parameters || []).map(p => `
                <details class="group bg-white rounded-3xl border border-slate-100 overflow-hidden mb-4 shadow-sm">
                  <summary class="flex items-center justify-between p-6 cursor-pointer list-none select-none">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-open:bg-indigo-50 group-open:text-indigo-600 transition-colors"><i class="fa-solid fa-list-check text-xs"></i></div>
                        <span class="text-[15px] font-black text-slate-700 tracking-tight">${esc(p.name)}</span>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform duration-300 group-open:rotate-180"></i>
                  </summary>
                  <div class="px-6 pb-6 pt-0 bg-slate-50/50">
                    <div class="border-t border-slate-100 pt-4"></div>
                    ${(p.labels || []).map(l => `
                    <details class="group/label bg-white/60 rounded-2xl border border-slate-100/50 overflow-hidden mb-3">
                        <summary class="flex items-center justify-between px-5 py-4 cursor-pointer list-none select-none">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100/50 flex items-center justify-center text-slate-400 group-open/label:bg-purple-50 group-open/label:text-purple-600 transition-colors"><i class="fa-solid fa-tag text-[10px]"></i></div>
                                <span class="text-sm font-bold text-slate-600 tracking-tight">${esc(l.name)}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-300 transition-transform duration-300 group-open/label:rotate-180"></i>
                        </summary>
                        <div class="px-5 pb-5 pt-0 bg-slate-50/30 space-y-3">
                            <div class="border-t border-slate-100/30 pt-3"></div>
                            ${(l.indicators || []).map(i => `
                            <div class="p-5 bg-white/50 rounded-2xl border border-slate-100/50 hover:border-blue-200 transition-all" data-indicator-id="${i.id}">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 mt-0.5"><i class="fa-solid fa-circle-check text-[10px]"></i></div>
                                        <div>
                                            <div class="text-[13px] font-bold text-slate-700 leading-snug"><span class="uppercase tracking-tight text-blue-600">${esc(i.code||('S'+i.id))}:</span> ${esc(i.title)}</div>
                                            <div class="mt-3 flex flex-wrap gap-2 files-list" data-id="${i.id}">${spinner()}</div>
                                        </div>
                                    </div>
                                    <button class="upload-btn w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 active:scale-95 transition-all" data-id="${i.id}"><i class="fa fa-cloud-arrow-up text-sm"></i></button>
                                </div>
                                <input type="file" class="hidden file-input" data-id="${i.id}" />
                            </div>`).join('')}
                        </div>
                    </details>`).join('')}
                  </div>
                </details>`).join('')}
            </div>
          </section>`).join('');
        document.querySelectorAll('[data-indicator-id]').forEach(el => loadIndicatorFiles(el.dataset.indicatorId));
      }

      async function chooseProgram(p) {
        window.activeProgramId = p.id;
        qs('#programBtnLabel').textContent = (p.code || p.name);
        qs('#activeProgram').textContent = `${p.code || ''}${p.name ? ' — ' + p.name : ''}`;
        
        try {
          loader.classList.remove('hidden');
          // 1. Load Admin Tasks
          const resT = await fetch(`tasks_api.php?program_id=${p.id}`);
          const jsonT = await resT.json();
          const rows = jsonT.data || [];
          if (rows.length > 0) {
            qs('#assignRows').innerHTML = rows.map(r => `<tr class="hover:bg-slate-50/50 border-b border-slate-50 last:border-0"><td class="px-8 py-5 text-sm font-bold text-slate-700 uppercase tracking-tight">${esc(r.title)}</td><td class="px-8 py-5 text-center"><span class="px-2 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase ring-1 ring-indigo-100">${esc(r.status)}</span></td><td class="px-8 py-5 text-right"><a class="w-9 h-9 inline-flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 transition-all shadow-sm" href="task_view.php?id=${r.id}"><i class="fa-solid fa-eye text-xs"></i></a></td></tr>`).join('');
            qs('#taskCount').textContent = `${rows.length} TASKS`;
            qs('#adminTasksCard').classList.remove('hidden');
          } else {
            qs('#adminTasksCard').classList.add('hidden');
          }

          // 2. Load Hierarchy
          const resL = await fetch(`level_programs_api.php?program_id=${p.id}`);
          const jsonL = await resL.json();
          const levels = jsonL.data || [];
          if (levels[0]) {
            const lid = levels[0].level_id || levels[0].id;
            qs('#activeLevel').textContent = levels[0].name;
            const resTree = await fetch(`section_tree_api.php?level_id=${lid}`);
            const jsonTree = await resTree.json();
            renderTree(jsonTree.data ? (jsonTree.data.sections || []) : []);
          } else {
            qs('#activeLevel').textContent = 'No Level'; renderTree([]);
          }
          
          loader.classList.add('hidden'); emptyState.classList.add('hidden'); mirrorWrap.classList.remove('hidden');
        } catch(e) {
          console.error(e);
          loader.classList.add('hidden'); emptyState.classList.remove('hidden');
        } finally { loader.classList.add('hidden'); }
      }

      async function init() {
        try {
          const res = await fetch('programs_api.php');
          const json = await res.json();
          const items = json.data || [];
          programMenu.innerHTML = items.map(p => `<div class="px-8 py-5 hover:bg-slate-50 cursor-pointer border-b border-slate-50 last:border-0 group" onclick="window._choose(${p.id})"><div class="font-black text-slate-900 text-base group-hover:text-blue-600 uppercase tracking-tight">${esc(p.code)}</div><div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">${esc(p.name)}</div></div>`).join('') || '<div class="p-8 text-center text-slate-400 text-xs">No programs found</div>';
          window._choose = (id) => { const p = items.find(x => x.id == id); if(p) { chooseProgram(p); programMenu.classList.add('hidden'); } };
          const initialId = new URLSearchParams(location.search).get('program_id');
          const pre = initialId ? items.find(p => p.id == initialId) : items[0];
          if (pre) chooseProgram(pre); else { loader.classList.add('hidden'); emptyState.classList.remove('hidden'); }
        } catch (e) { loader.classList.add('hidden'); emptyState.classList.remove('hidden'); }
      }

      programBtn.onclick = () => programMenu.classList.toggle('hidden');
      document.addEventListener('click', e => { if(!programBtn.contains(e.target) && !programMenu.contains(e.target)) programMenu.classList.add('hidden'); });
      document.addEventListener('click', e => { if (e.target.closest('.upload-btn')) e.target.closest('[data-indicator-id]').querySelector('.file-input').click(); });
      document.addEventListener('change', async e => {
        const i = e.target.closest('.file-input');
        if (i && i.files[0]) {
          const fd = new FormData(); fd.append('action', 'upload'); fd.append('indicator_id', i.dataset.id); fd.append('file', i.files[0]);
          const r = await fetch('indicator_documents_api.php', { method: 'POST', body: fd });
          if ((await r.json()).ok) loadIndicatorFiles(i.dataset.id);
        }
      });
      init();
    })();
  </script>

  <!-- Coordinator Modal Setup -->
  <div id="assignCoordModal" class="modal hidden">
    <div class="modal-backdrop bg-slate-900/50 backdrop-blur-sm" data-close="true"></div>
    <div class="modal-card w-[720px] border-0 rounded-[2.5rem] shadow-2xl overflow-hidden p-0">
      <div class="bg-gradient-to-r from-blue-700 to-indigo-600 px-8 py-6 flex items-center justify-between text-white">
        <h3 class="text-xl font-black uppercase tracking-tight">Personnel</h3>
        <button onclick="document.getElementById('assignCoordModal').classList.add('hidden'); document.body.classList.remove('overflow-hidden');" class="hover:text-white/60 transition-colors"><i class="fa fa-times"></i></button>
      </div>
      <div class="p-8 grid md:grid-cols-2 gap-8">
        <div><label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-4 ml-1">Team</label><ul id="acCurrent" class="space-y-2 max-h-[320px] overflow-auto custom-scrollbar"></ul></div>
        <div><label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-4 ml-1">Personnel</label><ul id="acEligible" class="space-y-2 max-h-[320px] overflow-auto custom-scrollbar"></ul></div>
      </div>
      <div class="px-8 py-6 border-t flex justify-end"><button onclick="document.getElementById('assignCoordModal').classList.add('hidden'); document.body.classList.remove('overflow-hidden');" class="px-8 py-3 rounded-2xl bg-slate-900 text-white text-xs font-black uppercase tracking-widest active:scale-95 transition-all">Done</button></div>
    </div>
  </div>

  <script>
    (function() {
      const modal = document.getElementById('assignCoordModal');
      const acCurrent = document.getElementById('acCurrent'), acEligible = document.getElementById('acEligible');
      async function fetchData() {
        const id = window.activeProgramId; if(!id) return;
        const res = await fetch(`coordinators_api.php?program_id=${id}`);
        const j = await res.json();
        if(j.ok) {
            acCurrent.innerHTML = j.data.current.map(u => `<li class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl group hover:border-blue-200 transition-all shadow-sm"><div><div class="font-black text-slate-700 text-[13px] uppercase tracking-tight">${u.first_name} ${u.last_name}</div><div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${u.email}</div></div><button onclick="window._acPost(${u.user_id},'DELETE')" class="text-rose-600 opacity-0 group-hover:opacity-100 transition-all"><i class="fa-solid fa-user-minus"></i></button></li>`).join('') || '<li class="p-8 text-center text-slate-300 text-[10px] font-bold uppercase italic bg-slate-50 rounded-2xl border border-dashed border-slate-200">No personnel assigned</li>';
            acEligible.innerHTML = j.data.eligible.map(u => `<li class="flex items-center justify-between p-4 bg-slate-50/50 border border-transparent rounded-2xl hover:bg-white hover:border-blue-100 transition-all group"><div><div class="font-black text-slate-700 text-[13px] uppercase tracking-tight">${u.first_name} ${u.last_name}</div><div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${u.email}</div></div><button onclick="window._acPost(${u.user_id},'POST')" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-[10px] font-black uppercase shadow-lg shadow-blue-200 transition-all active:scale-95">Assign</button></li>`).join('');
        }
      }
      window._acPost = (u, m) => fetch('coordinators_api.php', { method: m, body: m === 'POST' ? JSON.stringify({ program_id: window.activeProgramId, user_id: u }) : new URLSearchParams({ program_id: window.activeProgramId, user_id: u }) }).then(() => fetchData());
      document.getElementById('assignCoordBtn').onclick = () => { modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); fetchData(); };
    })();
  </script>
</body>
</html>