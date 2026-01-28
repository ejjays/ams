<?php
require_once __DIR__ . '/auth_guard.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tasks</title>
  <link rel="stylesheet" href="../app/css/dashboard.css?v=3">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
  <div class="min-h-screen flex">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="flex-1 overflow-auto">
      <header class="px-10 py-6 border-b bg-white">
        <div class="flex items-center justify-between gap-4 flex-wrap">
          <h1 class="text-2xl font-semibold">TASKS</h1>

          <div class="flex items-center justify-end gap-3">
            <button id="assignCoordBtn" class="inline-flex items-center gap-2 px-3 py-2 rounded-none-none bg-blue-600 hover:bg-blue-700 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition h-10 rounded-none">
              <i class="fa fa-user-plus"></i>
              <span>Assign Coordinator</span>
            </button>
            <div class="relative">
              <button id="programBtn" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none" aria-haspopup="listbox" aria-expanded="false">
                <span id="programBtnLabel">Choose program</span>
                <i class="fa fa-chevron-down text-xs opacity-80"></i>
              </button>
              <div id="programMenu" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border z-50 hidden" role="listbox" aria-labelledby="programBtn"></div>

            </div>
          </div>
        </div>
      </header>

      <section id="content" class="p-6">
        <div id="taskLoader" class="text-center p-10 text-gray-500">
          <i class="fa-solid fa-spinner fa-spin text-3xl"></i>
          <p class="mt-3">Loading tasks...</p>
        </div>
        <div id="realEmptyState" class="border border-dashed border-gray-300 rounded-xl p-10 bg-gray-50 text-gray-700 hidden">
          <strong>No content yet.</strong>
          <p class="mt-1">Pick a program from the dropdown to view tasks.</p>
        </div>

        <div id="mirrorWrap" class="hidden bg-white rounded-xl shadow-sm overflow-hidden border">
          <div class="border-b px-5 py-3 text-base md:text-lg text-gray-700 flex items-center gap-3">
            <span class="uppercase tracking-wide text-gray-500">Program:</span>
            <span id="activeProgram" class="font-semibold text-gray-800"></span>
            <span id="activeLevel" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs border text-green-700 border-green-300 bg-green-50"></span>
          </div>
          <div id="mirrorBody" class="p-4"></div>
        </div>



        <script>
          (function() {
            // Elements
            const qs = (sel, el = document) => el.querySelector(sel);
            const qsa = (sel, el = document) => Array.from(el.querySelectorAll(sel));
            const programBtn = qs('#programBtn');
            const programMenu = qs('#programMenu');
            const label = qs('#programBtnLabel');
            const assignRows = qs('#assignRows');
            const loader = qs('#taskLoader');
            const emptyState = qs('#realEmptyState');

            if (new URLSearchParams(window.location.search).has('program_id')) {
              emptyState.classList.add('hidden');
            }
            const listWrap = qs('#listWrap');
            const activeProgEl = qs('#activeProgram');

            let programs = [];
            let chosen = null;

            function setOpen(open) {
              programMenu.classList.toggle('hidden', !open);
              programBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function renderMenu(items) {
              programMenu.innerHTML = '';
              if (!items || items.length === 0) {
                programMenu.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No programs</div>';
                return;
              }
              items.forEach(p => {
                const div = document.createElement('div');
                div.className = 'px-4 py-3 text-sm hover:bg-gray-50 cursor-pointer';
                div.setAttribute('role', 'option');
                div.innerHTML = `<div class="font-medium">${p.code ? p.code : ''}</div><div class="text-xs text-gray-500">${p.name||''}</div>`;
                div.addEventListener('click', () => {
                  chooseProgram(p);
                  setOpen(false);
                });
                programMenu.appendChild(div);
              });
            }

            function fmtDate(d) {
              if (!d) return '—';
              const dt = new Date(d);
              if (isNaN(dt)) return d;
              return dt.toLocaleDateString(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
              });
            }

            function renderTasks(rows) {
              assignRows.innerHTML = '';
              if (!rows || rows.length === 0) return;
              rows.forEach(r => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                const title = (r.title || ('#' + r.id));
                const status = r.status || '—';
                const due = fmtDate(r.due_date);
                const created = fmtDate(r.created_at);
                tr.innerHTML = `
          <td class="px-4 py-3 font-medium">${title}</td>
          <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100">${status}</span></td>
          <td class="px-4 py-3">${due}</td>
          <td class="px-4 py-3">${created}</td>
          <td class="px-4 py-3 text-right"><a class="text-blue-600 hover:underline" href="task_view.php?id=${r.id||0}">View</a></td>`;
                assignRows.appendChild(tr);
              });
            }

            async function loadTasks(programId) {
              try {
                const res = await fetch('tasks_api.php?program_id=' + encodeURIComponent(programId));
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Failed to load tasks');
                renderTasks(json.data || []);
              } catch (err) {
                console.error(err);
                renderTasks([]);
              }
            }

            // --- Sections integration ---
            let currentLevelId = null;

            function renderSections(sections) {
              const wrap = document.getElementById('sectionsWrap');
              const list = document.getElementById('sectionsList');
              const count = document.getElementById('sectionsCount');
              if (!wrap || !list) return;
              list.innerHTML = '';
              if (!sections || sections.length === 0) {
                count.textContent = '(no sections)';
                wrap.classList.remove('hidden');
                return;
              }
              count.textContent = `(${sections.length})`;
              sections.forEach(s => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-xl border p-4 shadow-sm';
                const name = s.name || `Section #${s.id}`;
                const desc = s.description ? `<div class="text-xs text-gray-500 mt-1">${s.description}</div>` : '';
                card.innerHTML = `<div class="font-semibold">${name}</div>${desc}`;
                list.appendChild(card);
              });
              wrap.classList.remove('hidden');
            }
            async function loadSections(levelId) {
              // also load full tree for mirror
              try {
                const res2 = await fetch('section_tree_api.php?level_id=' + encodeURIComponent(levelId));
                const json2 = await res2.json();
                if (json2 && json2.ok) {
                  renderTree(json2.data && json2.data.sections ? json2.data.sections : []);
                  const lw = document.getElementById('listWrap');
                  if (lw) lw.classList.add('hidden');
                  return;
                }
              } catch (e) {
                console.error(e);
                renderTree([]);
              }

              currentLevelId = levelId;
              const wrap = document.getElementById('sectionsWrap');
              if (wrap) wrap.classList.add('hidden');
              try {
                if (!levelId) {
                  renderSections([]);
                  return;
                }
                const res = await fetch('sections_api.php?level_id=' + encodeURIComponent(levelId));
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Failed to load sections');
                // Normalize payload shapes: {data:[..]} or {data:{items:[..]}} or {items:[..]}
                let items = [];
                if (Array.isArray(json.data)) {
                  items = json.data;
                } else if (json.data && Array.isArray(json.data.items)) {
                  items = json.data.items;
                } else if (Array.isArray(json.items)) {
                  items = json.items;
                }
                renderSections(items);
              } catch (err) {
                console.error(err);
                renderSections([]);
              }
            }

            async function loadLevel(programId) {
              try {
                const res = await fetch(`level_programs_api.php?program_id=${encodeURIComponent(programId)}`);
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Failed loading level');
                const levels = json.data || [];
                const el = document.getElementById('activeLevel');
                if (!el) return;
                if (levels.length === 0) {
                  el.textContent = 'No level';
                  el.className = 'ml-4 inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs md:text-sm font-medium';
                  loadSections(null);
                  return;
                }
                const label = levels.map(l => l.name).join(', ');
                el.textContent = label;
                el.className = 'ml-4 inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs md:text-sm font-medium';
                const first = levels[0];
                const levelId = first.level_id || first.id;
                loadSections(levelId);
              } catch (e) {
                const el = document.getElementById('activeLevel');
                if (el) {
                  el.textContent = 'Level: error';
                  el.className = 'ml-4 inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs md:text-sm font-medium';
                }
                console.error(e);
                loadSections(null);
              }
            }

            function chooseProgram(p) {
              chosen = p;
              // ensure other components (e.g., Assign Coordinator modal) know current program
              window.activeProgramId = p.id;
              label.textContent = (p.code || p.name || 'Program');
              activeProgEl.textContent = `${p.code || ''}${p.name ? ' — ' + p.name : ''}`;
              if (loader) loader.classList.add('hidden'); // Itago ang loader
              if (emptyState) emptyState.classList.add('hidden'); // Itago rin ang empty state
              const url = new URL(window.location.href);
              url.searchParams.set('program_id', p.id);
              history.replaceState(null, '', url.toString());
              loadTasks(p.id);
              loadLevel(p.id);
            }

            programBtn.addEventListener('click', () => setOpen(programMenu.classList.contains('hidden')));
            document.addEventListener('click', (e) => {
              if (!programMenu.contains(e.target) && !programBtn.contains(e.target)) setOpen(false);
            });

            async function loadPrograms(initialId) {
              try {
                const res = await fetch('programs_api.php');
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Failed to load programs');
                const items = json.data || [];
                programs = items;
                renderMenu(programs);
                const pre = initialId ? programs.find(p => String(p.id) === String(initialId)) : programs[0];
                if (pre) {
                  window.activeProgramId = pre.id;
                  chooseProgram(pre);
                } else {
                  // ITO ANG IDAGDAG: kung walang programs
                  if (loader) loader.classList.add('hidden');
                  if (emptyState) emptyState.classList.remove('hidden');
                }
              } catch (err) {
                console.error(err);
                renderMenu([]);
                // ITO ANG IDAGDAG: kung may error
                if (loader) loader.classList.add('hidden');
                if (emptyState) emptyState.classList.remove('hidden');
              }
            }

            const params = new URLSearchParams(window.location.search);
            const initialId = params.get('program_id');

            // --- Mirror renderer (Section -> Parameters -> Labels -> Indicators)
            function esc(s) {
              return (s == null ? '' : String(s)).replace(/[&<>"']/g, m => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
              } [m]));
            }

            function renderTree(sections) {
              const wrap = document.getElementById('mirrorWrap');
              const body = document.getElementById('mirrorBody');
              if (!wrap || !body) return;
              if (loader) loader.classList.add('hidden');
              if (!sections || sections.length === 0) {
                body.innerHTML = '<div class="text-sm text-gray-500 p-3">No sections yet.</div>';
                wrap.classList.remove('hidden');
                return;
              }

              body.innerHTML = sections.map(sec => {
                const secName = esc(sec.name || `Section #${sec.id}`);
                const secDesc = sec.description ? `<div class=\"text-sm text-gray-600 mt-1\">${esc(sec.description)}</div>` : '';
                const params = Array.isArray(sec.parameters) ? sec.parameters : [];
                const paramsHtml = params.map(p => {
                  const labels = Array.isArray(p.labels) ? p.labels : [];
                  const labelsHtml = labels.map(l => {
                    const inds = Array.isArray(l.indicators) ? l.indicators : [];
                    const indList = inds.length ? inds.map(i => `
            <div class=\"px-3 py-2 bg-white rounded border\" data-indicator-id="${i.id}">
              <div class=\"flex items-center justify-between gap-3\">
                <div class=\"text-sm\"><span class=\"font-medium\">${esc(i.code||('S'+i.id))}:</span> ${esc(i.title||'')}</div>
                <div class=\"shrink-0 flex items-center gap-2\">
                  <button class=\"upload-btn text-xs px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700\" data-id="${i.id}">
                    <i class=\"fa fa-upload mr-1\"></i> Upload
                  </button>
                </div>
              </div>
              <input type=\"file\" class=\"hidden file-input\" data-id="${i.id}" />
              <div class=\"mt-2 text-xs text-gray-600 files-list\" data-id="${i.id}">Loading files...</div>
            </div>`).join('') :
                      `<div class=\"text-sm text-gray-500\">No indicators yet.</div>`;

                    return `
          <details class=\"group\">
            <summary class=\"flex items-center justify-between bg-blue-50 text-blue-800 px-3 py-2 rounded cursor-pointer\">
              <span class=\"font-medium\">${esc(l.name)}</span>
              <i class=\"fa-solid fa-chevron-down transition-transform group-open:rotate-180\"></i>
            </summary>
            <div class=\"p-3 space-y-2\">${indList}</div>
          </details>`;
                  }).join('');

                  return `
        <details class=\"group\">
          <summary class=\"flex items-center justify-between bg-slate-100 px-3 py-2 rounded cursor-pointer\">
            <span class=\"font-semibold\">${esc(p.name)}</span>
            <i class=\"fa-solid fa-chevron-down transition-transform group-open:rotate-180\"></i>
          </summary>
          <div class=\"p-3 space-y-2\">${labelsHtml || '<div class=\\"text-sm text-gray-500\\">No labels yet.</div>'}</div>
        </details>`;
                }).join('');

                return `
      <section class=\"mb-4 border rounded-xl overflow-hidden\">
        <div class=\"px-4 py-3 bg-white border-b\">
          <div class=\"text-lg font-semibold\">${secName}</div>
          ${secDesc}
        </div>
        <div class=\"p-3 space-y-2\">${paramsHtml || '<div class=\\"text-sm text-gray-500\\">No parameters yet.</div>'}</div>
      </section>`;
              }).join('');

              wrap.classList.remove('hidden');

              // NEW: I-trigger ang pag-load ng files pagkatapos ma-render ang lahat ng HTML
              loadAllIndicatorFiles();
            }

            loadPrograms(initialId);
          })();
        </script>

        <script>
          // ---- NEW FUNCTION: Load all files for all indicators ----
          function loadAllIndicatorFiles() {
            document.querySelectorAll('[data-indicator-id]').forEach(el => {
              const id = el.getAttribute('data-indicator-id');
              loadIndicatorFiles(id);
            });
          }
          // --------------------------------------------------------

          // ---- Indicator Upload Handlers ----
          const API_INDICATOR_DOCS = 'indicator_documents_api.php';

          document.addEventListener('click', async (e) => {
            const upBtn = e.target.closest('.upload-btn');
            if (upBtn) {
              const id = upBtn.getAttribute('data-id');
              const card = upBtn.closest('[data-indicator-id]');
              const input = card.querySelector('.file-input');
              input.click();
            }
          });

          document.addEventListener('change', async (e) => {
            const input = e.target.closest('.file-input');
            if (input && input.files && input.files[0]) {
              const id = input.getAttribute('data-id');
              const fd = new FormData();
              fd.append('action', 'upload');
              fd.append('indicator_id', id);
              fd.append('file', input.files[0]);
              try {
                const res = await fetch(API_INDICATOR_DOCS, {
                  method: 'POST',
                  body: fd
                });
                const j = await res.json();
                if (!j.ok) throw new Error(j.error || 'Upload failed');
                toast('Uploaded successfully.');
                // refresh list
                loadIndicatorFiles(id);
              } catch (err) {
                toast('Upload error: ' + err.message, true);
              } finally {
                input.value = '';
              }
            }
          });

          async function loadIndicatorFiles(indicatorId) {
            const listEl = document.querySelector(`.files-list[data-id="${indicatorId}"]`);
            if (!listEl) return;
            listEl.textContent = 'Loading files...';
            try {
              const res = await fetch(`${API_INDICATOR_DOCS}?action=list&indicator_id=${encodeURIComponent(indicatorId)}`);
              const j = await res.json();
              if (!j.ok) throw new Error(j.error || 'Failed to load');
              if (!Array.isArray(j.data) || j.data.length === 0) {
                listEl.textContent = 'No uploaded files yet.';
                return;
              }
              listEl.innerHTML = j.data.map(d => {
                const name = d.title || d.original_name || ('Document #' + d.doc_id);
                // Link to the stored file path
                const href = `../uploads/documents/${d.stored_name}`;
                const sizeKB = Math.max(1, Math.round((d.file_size || 0) / 1024));
                return `<a class="underline hover:no-underline mr-3" href="${href}" target="_blank">${name}</a><span class="text-gray-400 mr-2">(${sizeKB} KB)</span>`;
              }).join('<br/>');
            } catch (err) {
              listEl.textContent = 'Error loading files.';
            }
          }

          // tiny toast
          function toast(msg, isErr) {
            let t = document.getElementById('toast');
            if (!t) {
              t = document.createElement('div');
              t.id = 'toast';
              t.className = 'fixed bottom-4 right-4 px-3 py-2 rounded shadow text-white';
              document.body.appendChild(t);
            }
            t.textContent = msg;
            t.style.background = isErr ? '#dc2626' : '#16a34a';
            t.style.opacity = '1';
            setTimeout(() => t.style.opacity = '0', 2200);
          }

          /*
          // OLD CODE REMOVED: Inalis ang setTimeout block para maiwasan ang race condition
          // Ito ang dating block na nagdudulot ng problema dahil hindi pa fully rendered ang DOM
          // kaya't naitawag na ang loadIndicatorFiles() bago pa man ma-insert ang elements.
          // Ang function call ay nilipat sa dulo ng renderTree().
          */
        </script>


        <div id="assignCoordModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[200]">
          <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl">
            <div class="flex items-center justify-between px-5 py-4 border-b">
              <h3 class="text-lg font-semibold">Assign Program Coordinator</h3>
              <button id="acClose" class="p-2 hover:bg-gray-100 rounded-lg"><i class="fa fa-times"></i></button>
            </div>
            <div class="p-5">
              <div class="mb-4 text-sm text-gray-600">Program: <span id="acProgramName" class="font-medium"></span></div>
              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 class="font-medium mb-2">Current Coordinators</h4>
                  <ul id="acCurrent" class="divide-y border rounded-lg max-h-72 overflow-auto"></ul>
                </div>
                <div>
                  <h4 class="font-medium mb-2">Eligible Users</h4>
                  <div class="mb-2">
                    <input id="acSearch" type="text" placeholder="Search by name or email" class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring" />
                  </div>
                  <ul id="acEligible" class="divide-y border rounded-lg max-h-72 overflow-auto"></ul>
                </div>
              </div>
            </div>
            <div class="px-5 py-4 border-t text-right">
              <button id="acDone" class="px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition">Done</button>
            </div>
          </div>
        </div>



        <script>
          (function() {
            const modal = document.getElementById('assignCoordModal');
            const btn = document.getElementById('assignCoordBtn');
            const acClose = document.getElementById('acClose');
            const acDone = document.getElementById('acDone');
            const acCurrent = document.getElementById('acCurrent');
            const acEligible = document.getElementById('acEligible');
            const acSearch = document.getElementById('acSearch');
            const acProgramName = document.getElementById('acProgramName');

            let currentProgram = null;

            function getProgramContext() {
              const el = document.getElementById('programBtnLabel');
              return {
                id: window.activeProgramId || null,
                name: el ? el.textContent.trim() : ''
              };
            }

            async function fetchData() {
              const ctx = getProgramContext();
              if (!ctx.id) {
                alert('Choose a program first.');
                return;
              }
              acProgramName.textContent = ctx.name;
              const res = await fetch('coordinators_api.php?program_id=' + encodeURIComponent(ctx.id));
              const json = await res.json();
              if (!json.ok) {
                alert(json.error || 'Failed to load');
                return;
              }
              renderLists(json.data.current, json.data.eligible);
            }

            function renderLists(current, eligible) {
              acCurrent.innerHTML = current.length ? '' : '<li class="p-3 text-sm text-gray-500">No coordinators assigned.</li>';
              current.forEach(u => {
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between p-3';
                li.innerHTML = '<div><div class="font-medium">' + u.first_name + ' ' + u.last_name + '</div><div class="text-xs text-gray-500">' + u.email + '</div></div>' +
                  '<button data-user="' + u.user_id + '" class="remove px-3 py-1.5 rounded-xl border border-red-300 text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">Remove</button>';

                acCurrent.appendChild(li);
              });

              function renderEligible(filter = '') {
                acEligible.innerHTML = '';
                const f = filter.toLowerCase();
                eligible.filter(u => (u.first_name + ' ' + u.last_name + ' ' + u.email).toLowerCase().includes(f)).forEach(u => {
                  const li = document.createElement('li');
                  li.className = 'flex items-center justify-between p-3';
                  li.innerHTML = '<div><div class="font-medium">' + u.first_name + ' ' + u.last_name + '</div><div class="text-xs text-gray-500">' + u.email + '</div></div>' +
                    '<button data-user="' + u.user_id + '" class="assign px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition">Assign</button>';



                  acEligible.appendChild(li);
                });
              }
              renderEligible();
              acSearch.oninput = () => renderEligible(acSearch.value);
            }

            async function assign(userId) {
              const ctx = getProgramContext();
              const res = await fetch('coordinators_api.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                  program_id: ctx.id,
                  user_id: userId
                })
              });
              const j = await res.json();
              if (!j.ok) {
                alert(j.error || 'Failed');
                return;
              }
              await fetchData();
            }
            async function removeUser(userId) {
              const ctx = getProgramContext();
              const res = await fetch('coordinators_api.php', {
                method: 'DELETE',
                body: new URLSearchParams({
                  program_id: ctx.id,
                  user_id: userId
                })
              });
              const j = await res.json();
              if (!j.ok) {
                alert(j.error || 'Failed');
                return;
              }
              await fetchData();
            }

            acCurrent.addEventListener('click', (e) => {
              const btn = e.target.closest('button.remove');
              if (btn) {
                removeUser(btn.getAttribute('data-user'));
              }
            });
            acEligible.addEventListener('click', (e) => {
              const btn = e.target.closest('button.assign');
              if (btn) {
                assign(btn.getAttribute('data-user'));
              }
            });

            btn && btn.addEventListener('click', () => {
              modal.classList.remove('hidden');
              modal.classList.add('flex');
              fetchData();
            });
            acClose && acClose.addEventListener('click', () => {
              modal.classList.add('hidden');
              modal.classList.remove('flex');
            });
            acDone && acDone.addEventListener('click', () => {
              modal.classList.add('hidden');
              modal.classList.remove('flex');
            });

            // expose program id used elsewhere on page if available
            // hook into existing code that sets program id if present
            if (!window.activeProgramId) {
              // Try to parse from URL param
              const url = new URL(location.href);
              const pid = url.searchParams.get('program_id');
              if (pid) window.activeProgramId = pid;
            }
          })();
        </script>

</body>

</html>