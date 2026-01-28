/* app/js/levels.js  — rebuilt (vanilla JS + Tailwind)
 * Features:
 * - Render Level cards from levels_api.php?instrument_id=...
 * - Show tagged Programs as chips per card (single source of truth: level_programs_api.php)
 * - Per-card Tag button opens Add-only modal
 * - Untag via chip × (refreshes only that card)
 * - Create/Edit/Delete Level (uses existing form & modal in level.php)
 *
 * API contracts in this project:
 *   GET  levels_api.php?instrument_id=:iid              → { ok, data:{ items:[{id,name,description,weight?}] } }
 *   POST levels_api.php  (form-encoded)                 → create/update (expects id?, instrument_id, name, description, weight?)
 *   DEL  levels_api.php?id=:id                          → delete
 *   GET  level_programs_api.php?level_id=:id            → { ok, data:[ { id or program_id, name } ] }
 *   POST level_programs_api.php  (JSON)                 → { level_id, program_id }  (idempotent)
 *   DEL  level_programs_api.php?level_id=:id&program_id=:pid → untag
 *   GET  programs_api.php?action=list                   → { ok, data:[ { program_id, name } ] }
 */

(function(){
  const API_LEVELS    = 'levels_api.php';
  const API_LP        = 'level_programs_api.php';
  const API_PROGRAMS  = 'programs_api.php?action=list';

  const params        = new URLSearchParams(location.search);
  const instrumentId  = params.get('instrument_id');

  // DOM
  const listEl        = document.getElementById('levelList');
  const openBtn       = document.getElementById('openCreateLevel');
  const modal         = document.getElementById('levelModal');
  const closeX        = document.getElementById('levelCloseX');
  const cancelBtn     = document.getElementById('levelCreateCancel');
  const form          = document.getElementById('levelCreateForm');
  const idIn          = document.getElementById('level_id');
  const nameIn        = document.getElementById('level_name');
  const weightIn      = document.getElementById('level_weight');
  const tagModeBtn    = document.getElementById('openTagProgramLevel');

  // Helpers
  async function parseJsonSafe(res){
    try { return await res.json(); } catch { return { ok:false, error:`HTTP ${res.status} ${res.statusText}` }; }
  }
  function esc(s){ const d=document.createElement('div'); d.textContent=String(s??''); return d.innerHTML; }
  function spinner(w=24,h=24){ return `<svg aria-hidden="true" class="animate-spin" width="${w}" height="${h}" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" fill="none"></path></svg>`; }

  // Chips
  function chipHtml(levelId, prog){
    const pid = Number(prog.program_id ?? prog.id);
    const name = esc(prog.name ?? '');
    return `<span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-gray-100 program-chip">
      <span class="program-name">${name}</span>
      <button class="rounded hover:bg-gray-200 px-1" aria-label="Untag" data-action="untag" data-level-id="${levelId}" data-program-id="${pid}">×</button>
    </span>`;
  }

  async function fetchLevelPrograms(levelId){
    const res = await fetch(`${API_LP}?level_id=${encodeURIComponent(levelId)}&t=${Date.now()}`, { credentials:'same-origin' });
    const json = await parseJsonSafe(res);
    if (json && json.ok) return Array.isArray(json.data) ? json.data : [];
    throw new Error(json && json.error || 'Failed to load level programs');
  }

  async function renderLevelChips(levelId){
    const c = document.querySelector(`[data-chips-for="${CSS.escape(String(levelId))}"]`);
    if (!c) return;
    c.innerHTML = `<span class="text-xs text-slate-400 inline-flex items-center gap-2">${spinner(16,16)} Loading…</span>`;
    try{
      const rows = await fetchLevelPrograms(levelId);
      c.innerHTML = rows.length ? rows.map(r => chipHtml(levelId, r)).join('') : `<span class="text-xs text-slate-400">None yet</span>`;
    }catch(e){
      console.error(e);
      c.innerHTML = `<span class="text-xs text-red-500">Failed to load</span>`;
    }
  }

  async function untagProgram(levelId, programId){
    const url = `${API_LP}?level_id=${encodeURIComponent(levelId)}&program_id=${encodeURIComponent(programId)}`;
    const res = await fetch(url, { method:'DELETE', credentials:'same-origin' });
    const json = await parseJsonSafe(res);
    if (!json.ok) throw new Error(json.error || 'Failed to untag');
  }

  // Modal (Add only)
  const tagModal        = document.getElementById('tagProgramLevelModal');
  const tagModalClose   = document.getElementById('tagProgramLevelModalClose');
  const tagModalCancel  = document.getElementById('tagProgramLevelModalCancel');
  const tagModalForm    = document.getElementById('tagProgramLevelModalForm');
  const tagModalSelect  = document.getElementById('tagProgramLevelModalProgram');
  const tagModalCtx     = document.getElementById('tagProgramLevelContext');
  let currentLevelId    = null;

  function showTagModal(){ if(tagModal) tagModal.classList.remove('hidden'); }
  function hideTagModal(){ if(tagModal) tagModal.classList.add('hidden'); currentLevelId=null; if (tagModalSelect) tagModalSelect.innerHTML=''; }

  async function openTagModal(levelId, levelName){
    if (!tagModal || !tagModalSelect) return;
    currentLevelId = levelId;
    if (tagModalCtx) tagModalCtx.textContent = levelName ? `Level: ${levelName}` : '';
    showTagModal();

    // load all programs + already-tagged, filter to untagged only
    try {
      const [progsRes, tagged] = await Promise.all([
        fetch(API_PROGRAMS, { credentials:'same-origin' }).then(parseJsonSafe),
        fetchLevelPrograms(levelId)
      ]);
      const all = (progsRes && progsRes.ok && Array.isArray(progsRes.data)) ? progsRes.data : [];
      const taggedIds = new Set(tagged.map(p => Number(p.program_id ?? p.id)));
      const untagged = all.filter(p => !taggedIds.has(Number(p.program_id ?? p.id)));
      tagModalSelect.innerHTML = '';
      if (untagged.length === 0){
        tagModalSelect.innerHTML = `<option value="">All programs already tagged</option>`;
      } else {
        tagModalSelect.appendChild(new Option('Select a program…', '', true, true));
        tagModalSelect.firstChild.disabled = true;
        for (const p of untagged){
          const pid = Number(p.program_id ?? p.id);
          tagModalSelect.appendChild(new Option(p.name, String(pid)));
        }
      }
    } catch (e){
      console.error(e);
      alert('Failed to load programs');
      hideTagModal();
    }
  }

  if (tagModalClose)  tagModalClose.addEventListener('click', hideTagModal);
  if (tagModalCancel) tagModalCancel.addEventListener('click', hideTagModal);

  if (tagModalForm){
    tagModalForm.addEventListener('submit', async (ev)=>{
      ev.preventDefault();
      const pid = parseInt(tagModalSelect && tagModalSelect.value || '0', 10);
      if (!currentLevelId || !pid){ alert('Please select a program'); return; }
      try {
        const res = await fetch(API_LP, {
          method:'POST',
          credentials:'same-origin',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify({ level_id: currentLevelId, program_id: pid })
        });
        const json = await parseJsonSafe(res);
        if (!json.ok) throw new Error(json.error || 'Failed to save');
        hideTagModal();
        await renderLevelChips(currentLevelId);
      } catch (e){
        console.error(e); alert(e.message || 'Failed to save');
      }
    });
  }

  // Level list (cards)

function cardHtml(it){
  const id = Number(it.id);
  const name = esc(it.name ?? `Level ${id}`);
  // Hindi na ipapakita ang description para maging payat
  // const desc = esc(it.description ?? ''); 
  const editButtonsClass = editMode ? '' : 'hidden'; 

  // Siguraduhing WALANG comments sa loob ng backticks
  return `<div class="panel px-4 py-4 level-card--compact hover:bg-gray-50 cursor-pointer" data-level-id="${id}" data-level-name="${name}">
    <div class="flex items-center justify-between gap-4 level-head"> 
      <div class="min-w-0 flex-shrink"> 
        <h3 class="text-lg font-semibold leading-snug truncate">${name}</h3> 
      </div>
      <div class="flex-grow"></div>
      <div class="flex-none flex items-center gap-3"> 
        <div class="flex flex-wrap gap-1 justify-end max-w-xs sm:max-w-sm md:max-w-md" data-chips-for="${id}"> 
        </div>
        <div class="flex items-center gap-1"> 
          <button class="p-2 rounded-md bg-gray-200 hover:bg-gray-300 text-slate-700" data-action="open-tag-modal" aria-label="Tag programs"><i class="fa-solid fa-tag"></i></button> 
          <button class="p-2 rounded-md bg-gray-200 hover:bg-gray-300 text-slate-700 ${editButtonsClass}" data-edit aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
          <button class="p-2 rounded-md bg-gray-200 hover:bg-gray-300 text-slate-700 ${editButtonsClass}" data-del aria-label="Delete"><i class="fa-solid fa-trash"></i></button>
        </div>
      </div>
    </div>
  </div>`; // <-- Dito nagtatapos ang backtick
}


  async function loadLevels(){
    if (!instrumentId){ listEl.innerHTML = '<div class="text-gray-500">Missing instrument_id in URL.</div>'; return; }

    listEl.innerHTML = `<div class="flex items-center gap-2 text-gray-500">${spinner()} <span>Loading levels…</span></div>`;
    try {
      const res = await fetch(`${API_LEVELS}?instrument_id=${encodeURIComponent(instrumentId)}&t=${Date.now()}`, { credentials:'same-origin' });
      const json = await parseJsonSafe(res);
      const items = (json && json.ok && json.data && Array.isArray(json.data.items)) ? json.data.items : [];
      listEl.innerHTML = items.length ? items.map(cardHtml).join('') : '<div class="text-gray-500">No levels yet.</div>';
      // render chips for each card
      document.querySelectorAll('[data-level-id]').forEach(card => {
        const id = parseInt(card.getAttribute('data-level-id'), 10);
        if (id) renderLevelChips(id);
      });
    } catch (e){
      console.error(e);
      listEl.innerHTML = '<div class="text-red-500">Failed to load levels.</div>';
    }
  }

  // Edit mode toggle (shows pencils & trash)
  let editMode = false;
  function applyEditMode(){
    document.querySelectorAll('[data-edit],[data-del]').forEach(btn => {
      btn.classList.toggle('hidden', !editMode);
    });
  }

  // Create/Edit
  function openModal(){ modal && modal.classList.remove('hidden'); }
  function closeModal(){ modal && modal.classList.add('hidden'); form && form.reset(); idIn.value=''; }

  if (openBtn)  openBtn.addEventListener('click', ()=>{ idIn.value=''; nameIn.value=''; openModal(); });
  if (closeX)   closeX.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

  if (form){
    form.addEventListener('submit', async (ev)=>{
      ev.preventDefault();
      const fd = new FormData(form);
      fd.set('instrument_id', instrumentId || '');
      try{
        const res = await fetch(API_LEVELS, { method:'POST', credentials:'same-origin', body: fd });
        const json = await parseJsonSafe(res);
        if (!json.ok) throw new Error(json.error || 'Failed to save');
        closeModal();
        await loadLevels();
      }catch(e){ console.error(e); alert(e.message || 'Failed to save'); }
    });
  }

  // Delegated actions inside list
  document.addEventListener('click', async (e)=>{
    // Open per-card tag modal
    const tagBtn = e.target.closest('[data-action="open-tag-modal"]');
    if (tagBtn){
      const card = tagBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      const name = card?.getAttribute('data-level-name') || '';
      if (id) openTagModal(id, name);
      return;
    }

    // Untag ×
    const unBtn = e.target.closest('[data-action="untag"]');
    if (unBtn){
      const lid = parseInt(unBtn.getAttribute('data-level-id')||'0', 10);
      const pid = parseInt(unBtn.getAttribute('data-program-id')||'0', 10);
      if (!lid || !pid) return;
      try {
        unBtn.disabled = true;
        await untagProgram(lid, pid);
        await renderLevelChips(lid);
      } catch (err){ console.error(err); alert(err.message || 'Failed to untag'); }
      finally { unBtn.disabled = false; }
      return;
    }

    // Edit
    const editBtn = e.target.closest('[data-edit]');
    if (editBtn){
      const card = editBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      const name = card?.querySelector('h3')?.textContent?.trim() || '';
      // const desc = ... (inalis na)
      if (id) {
        idIn.value = String(id); nameIn.value = name; openModal();
      }
      return;
    }

    // Delete
    const delBtn = e.target.closest('[data-del]');
    if (delBtn){
      const card = delBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      if (!id) return;
      if (!confirm('Delete this level?')) return;
      try{
        const res = await fetch(`${API_LEVELS}?id=${encodeURIComponent(id)}`, { method:'DELETE', credentials:'same-origin' });
        const json = await parseJsonSafe(res);
        if (!json.ok) throw new Error(json.error || 'Failed to delete');
        await loadLevels();
      }catch(err){ console.error(err); alert(err.message || 'Delete failed'); }
      return;
    }
  });


  // Navigate to sections when clicking a level card (ignore clicks on interactive controls)
  const levelList = document.getElementById('levelList');
  function shouldIgnoreClick(target){
    return !!target.closest('button, [data-action], [data-del], [data-edit], a, input, select, textarea, label');
  }
  levelList?.addEventListener('click', (e)=>{
    if (shouldIgnoreClick(e.target)) return;
    const card = e.target.closest('[data-level-id]');
    if (!card) return;
    const lid = card.getAttribute('data-level-id');
    if (lid) window.location.href = 'section.php?level_id=' + encodeURIComponent(lid);
  });
  levelList?.addEventListener('dblclick', (e)=>{
    if (shouldIgnoreClick(e.target)) return;
    const card = e.target.closest('[data-level-id]');
    if (!card) return;
    const lid = card.getAttribute('data-level-id');
    if (lid) window.location.href = 'section.php?level_id=' + encodeURIComponent(lid);
  });

  // Top bar buttons
  const editToggleBtn = document.getElementById('levelEditToggleBtn');
  if (editToggleBtn){
    editToggleBtn.addEventListener('click', ()=>{ editMode = !editMode; applyEditMode(); editToggleBtn.classList.toggle('ring-2', editMode); });
  }
  if (tagModeBtn){
    tagModeBtn.addEventListener('click', ()=>{
      // Give a light glow to tag buttons to guide the user
      document.querySelectorAll('[data-action="open-tag-modal"]').forEach(b=> b.classList.add('ring-2','ring-blue-500'));
      setTimeout(()=>document.querySelectorAll('[data-action="open-tag-modal"]').forEach(b=> b.classList.remove('ring-2','ring-blue-500')), 1200);
    });
  }

  // Init
  document.addEventListener('DOMContentLoaded', ()=>{
    loadLevels();
    applyEditMode();
  });
})();
