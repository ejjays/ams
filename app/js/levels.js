// app/js/levels.js — Modern list + program tagging for Levels
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
  const tagModeBtn    = document.getElementById('openTagProgramLevel');

  // Helpers
  async function parseJsonSafe(res){
    try { return await res.json(); } catch { return { ok:false, error:`HTTP ${res.status} ${res.statusText}` }; }
  }
  function esc(s){ const d=document.createElement('div'); d.textContent=String(s??''); return d.innerHTML; }
  function spinner(w=16,h=16){ return `<svg aria-hidden="true" class="animate-spin text-blue-600" width="${w}" height="${h}" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" fill="none"></path></svg>`; }

  // Chips
  function chipHtml(levelId, prog){
    const pid = Number(prog.program_id ?? prog.id);
    const name = esc(prog.name ?? '');
    return `<span class="inline-flex items-center gap-2 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-xl bg-blue-50 text-blue-700 ring-1 ring-blue-100 group/chip">
      <span>${name}</span>
      <button class="w-4 h-4 flex items-center justify-center rounded-full hover:bg-blue-200 transition-colors" aria-label="Untag" data-action="untag" data-level-id="${levelId}" data-program-id="${pid}">
        <i class="fa-solid fa-xmark text-[8px]"></i>
      </button>
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
    c.innerHTML = `<div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">${spinner()} Loading…</div>`;
    try{
      const rows = await fetchLevelPrograms(levelId);
      c.innerHTML = rows.length ? rows.map(r => chipHtml(levelId, r)).join('') : `<span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">No programs tagged</span>`;
    }catch(e){
      console.error(e);
      c.innerHTML = `<span class="text-[10px] font-bold text-rose-400 uppercase tracking-widest">Failed to load</span>`;
    }
  }

  async function untagProgram(levelId, programId){
    const url = `${API_LP}?level_id=${encodeURIComponent(levelId)}&program_id=${encodeURIComponent(programId)}`;
    const res = await fetch(url, { method:'DELETE', credentials:'same-origin' });
    const json = await parseJsonSafe(res);
    if (!json.ok) throw new Error(json.error || 'Failed to untag');
  }

  // Tag Modal
  const tagModal        = document.getElementById('tagProgramLevelModal');
  const tagModalClose   = document.getElementById('tagProgramLevelModalClose');
  const tagModalCancel  = document.getElementById('tagProgramLevelModalCancel');
  const tagModalForm    = document.getElementById('tagProgramLevelModalForm');
  const tagModalSelect  = document.getElementById('tagProgramLevelModalProgram');
  const tagModalCtx     = document.getElementById('tagProgramLevelContext');
  let currentLevelId    = null;

  function showTagModal(){ 
    tagModal?.classList.remove('hidden'); 
    document.body.classList.add('overflow-hidden');
  }
  function hideTagModal(){ 
    tagModal?.classList.add('hidden'); 
    document.body.classList.remove('overflow-hidden');
    currentLevelId=null; 
    if (tagModalSelect) tagModalSelect.innerHTML=''; 
  }

  async function openTagModal(levelId, levelName){
    if (!tagModal || !tagModalSelect) return;
    currentLevelId = levelId;
    if (tagModalCtx) tagModalCtx.textContent = levelName ? `LEVEL: ${levelName}` : '';
    showTagModal();

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
    const editButtonsClass = editMode ? '' : 'hidden'; 

    return `<div class="group relative flex flex-col p-8 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-blue-100/40 hover:border-blue-200 transition-all duration-300 cursor-pointer" data-level-id="${id}" data-level-name="${name}">
        <div class="flex items-center justify-between gap-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                    <i class="fa-solid fa-layer-group text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-1">Accreditation Stage</span>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight leading-none">${name}</h3>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-90" data-action="open-tag-modal" title="Tag Program">
                    <i class="fa-solid fa-tag text-sm pointer-events-none"></i>
                </button>
                <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-90 ${editButtonsClass}" data-edit title="Edit Level">
                    <i class="fa-solid fa-pen text-sm pointer-events-none"></i>
                </button>
                <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all active:scale-90 ${editButtonsClass}" data-del title="Delete Level">
                    <i class="fa-solid fa-trash text-sm pointer-events-none"></i>
                </button>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-50">
            <div class="flex flex-wrap gap-2" data-chips-for="${id}"></div>
        </div>
    </div>`;
  }

  async function loadLevels(){
    if (!instrumentId){ listEl.innerHTML = '<div class="col-span-full py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">Missing instrument_id in URL.</div>'; return; }

    listEl.innerHTML = `<div class="flex items-center justify-center py-20 gap-3 text-slate-400 font-black uppercase tracking-widest text-xs">${spinner(24,24)} <span>Synchronizing levels…</span></div>`;
    try {
      const res = await fetch(`${API_LEVELS}?instrument_id=${encodeURIComponent(instrumentId)}&t=${Date.now()}`, { credentials:'same-origin' });
      const json = await parseJsonSafe(res);
      const items = (json && json.ok && json.data && Array.isArray(json.data.items)) ? json.data.items : [];
      listEl.innerHTML = items.length ? items.map(cardHtml).join('') : '<div class="col-span-full py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">No levels defined for this instrument.</div>';
      
      document.querySelectorAll('[data-level-id]').forEach(card => {
        const id = parseInt(card.getAttribute('data-level-id'), 10);
        if (id) renderLevelChips(id);
      });
    } catch (e){
      console.error(e);
      listEl.innerHTML = '<div class="col-span-full py-20 text-center text-rose-500 font-black uppercase tracking-widest text-xs">System failed to retrieve level architecture.</div>';
    }
  }

  // Edit mode toggle
  let editMode = false;
  function applyEditMode(){
    document.querySelectorAll('[data-edit],[data-del]').forEach(btn => {
      btn.classList.toggle('hidden', !editMode);
    });
  }

  function openModal(){ 
    modal?.classList.remove('hidden'); 
    document.body.classList.add('overflow-hidden');
  }
  function closeModal(){ 
    modal?.classList.add('hidden'); 
    document.body.classList.remove('overflow-hidden');
    form?.reset(); 
    idIn.value=''; 
  }

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

  // Delegated actions
  document.addEventListener('click', async (e)=>{
    const tagBtn = e.target.closest('[data-action="open-tag-modal"]');
    if (tagBtn){
      const card = tagBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      const name = card?.getAttribute('data-level-name') || '';
      if (id) openTagModal(id, name);
      return;
    }

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

    const editBtn = e.target.closest('[data-edit]');
    if (editBtn){
      const card = editBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      const name = card?.getAttribute('data-level-name') || '';
      if (id) {
        idIn.value = String(id); nameIn.value = name; openModal();
      }
      return;
    }

    const delBtn = e.target.closest('[data-del]');
    if (delBtn){
      const card = delBtn.closest('[data-level-id]');
      const id = parseInt(card?.getAttribute('data-level-id')||'0', 10);
      if (!id) return;
      if (!confirm('Permanently remove this accreditation level?')) return;
      try{
        const res = await fetch(`${API_LEVELS}?id=${encodeURIComponent(id)}`, { method:'DELETE', credentials:'same-origin' });
        const json = await parseJsonSafe(res);
        if (!json.ok) throw new Error(json.error || 'Failed to delete');
        await loadLevels();
      }catch(err){ console.error(err); alert(err.message || 'Delete failed'); }
      return;
    }
  });

  // Navigation
  const levelList = document.getElementById('levelList');
  function shouldIgnoreClick(target){
    return !!target.closest('button, [data-action], [data-del], [data-edit], a, input, select, textarea, label, .program-chip');
  }
  levelList?.addEventListener('click', (e)=>{
    if (shouldIgnoreClick(e.target)) return;
    const card = e.target.closest('[data-level-id]');
    if (!card) return;
    const lid = card.getAttribute('data-level-id');
    if (lid) window.location.href = 'section.php?level_id=' + encodeURIComponent(lid);
  });

  // Top bar buttons
  const editToggleBtn = document.getElementById('levelEditToggleBtn');
  if (editToggleBtn){
    editToggleBtn.addEventListener('click', ()=>{ 
        editMode = !editMode; 
        applyEditMode(); 
        editToggleBtn.classList.toggle('bg-blue-600', editMode); 
        editToggleBtn.classList.toggle('text-white', editMode); 
    });
  }

  // Init
  document.addEventListener('DOMContentLoaded', ()=>{
    loadLevels();
  });
})();