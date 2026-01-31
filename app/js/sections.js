// app/js/sections.js — Modern hierarchical UI for Sections, Parameters, Labels, and Indicators
(function(){
  "use strict";

  const SECT_API  = 'sections_api.php', PARAM_API = 'parameters_api.php', LABEL_API = 'parameter_labels_api.php', IND_API = 'indicator_labels_api.php';
  const qs = (s,el=document)=>el.querySelector(s), qsa = (s,el=document)=>Array.from(el.querySelectorAll(s));
  const levelId = Number(new URLSearchParams(location.search).get('level_id')||0);
  
  function esc(s){
    const d = document.createElement('div');
    d.textContent = String(s == null ? '' : s);
    return d.innerHTML;
  }
  
  const spinner = (w=16,h=16) => `<svg aria-hidden="true" class="animate-spin text-blue-600" width="${w}" height="${h}" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" fill="none"></path></svg>`;

  const listEl = qs('#sectionList'), editToggleBtn = qs('#sectionEditToggleBtn'), sectionModal = qs('#sectionModal'), sectionForm = qs('#sectionCreateForm'), sectionIdIn = qs('#section_id'), sectionNameIn = qs('#section_name'), openCreateBtn = qs('#openCreateSection');
  const paramModal = qs('#parameterModal'), paramForm = qs('#paramForm'), paramSectionIdIn = qs('#param_section_id'), paramNameIn = qs('#param_name'), paramDescIn = qs('#param_description');
  const labelModal = qs('#labelModal'), labelForm = qs('#labelForm'), labelParamIdIn = qs('#label_param_id'), labelNameIn = qs('#label_name'), indModal = qs('#indicatorModal'), indForm = qs('#indicatorForm'), indLabelId = qs('#indicator_label_id'), indCode = qs('#indicator_code'), indTitle = qs('#indicator_title'), indEvidence = qs('#indicator_evidence');

  let editMode = false;
  function show(el){ if(el) { el.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); } }
  function hide(el){ if(el) { el.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); } }

  function sectionCard(s){
    const sid = Number(s.id), hidden = editMode ? '' : 'hidden';
    return `<div class="group flex flex-col bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm transition-all duration-300 section-card overflow-hidden" id="section-${sid}">
        <div class="flex items-center justify-between p-8 bg-white border-b border-transparent group-[.is-open]:border-slate-50 transition-colors">
          <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm group-[.is-open]:bg-blue-600 group-[.is-open]:text-white transition-all duration-300"><i class="fa-solid fa-folder-tree text-2xl"></i></div>
            <div><span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-1">Accreditation Section</span><h3 class="text-2xl font-black text-slate-900 tracking-tight leading-none">${esc(s.name)}</h3></div>
          </div>
          <div class="flex items-center gap-2">
            <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95" title="Add Parameter" data-add-parameter="${sid}"><i class="fa-solid fa-plus text-sm pointer-events-none"></i></button>
            <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-95" title="Expand View" data-toggle-parameters="${sid}"><i class="fa-solid fa-chevron-down text-xs pointer-events-none transition-transform duration-300" data-chevron="${sid}"></i></button>
            <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100/50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-90 ${hidden}" data-edit="${sid}" data-edit-name="${esc(s.name)}" title="Edit Section"><i class="fa-solid fa-pen text-sm pointer-events-none"></i></button>
            <button class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-100/50 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all active:scale-90 ${hidden}" data-del="${sid}" title="Delete Section"><i class="fa-solid fa-trash text-sm pointer-events-none"></i></button>
          </div>
        </div>
        <div id="params-${sid}" class="hidden bg-slate-50/50"></div>
      </div>`;
  }

  function renderParametersList(rows){
    if(!rows||rows.length===0) return `<div class="p-10 text-center"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">No criteria defined.</p></div>`;
    return `<div class="p-8 space-y-4">${rows.map(r=>`<details class="group bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm" data-parameter-id="${r.id}">
            <summary class="flex items-center justify-between p-6 cursor-pointer list-none select-none">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-open:bg-indigo-50 group-open:text-indigo-600"><i class="fa-solid fa-list-check text-xs"></i></div>
                    <span class="text-[15px] font-black text-slate-700 tracking-tight">${esc(r.name)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center" title="Add label" data-add-label="${r.id}"><i class="fa-solid fa-plus text-[10px]"></i></button>
                    <button class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-blue-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Edit" data-edit-parameter="${r.id}" data-current-name="${esc(r.name)}"><i class="fa-solid fa-pen text-[10px]"></i></button>
                    <button class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-rose-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Delete" data-del-parameter="${r.id}"><i class="fa-solid fa-trash text-[10px]"></i></button>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-300 transition-transform group-open:rotate-180 ml-2"></i>
                </div>
            </summary>
            <div id="labels-${r.id}" class="px-6 pb-6 bg-slate-50/50"><div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">${spinner()} Loading structural labels…</div></div>
        </details>`).join('')}</div>`;
  }

  function renderLabelsList(rows){
    if(!rows||rows.length===0) return `<div class="p-4 text-center border-t border-slate-100"><p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">No structural labels.</p></div>`;
    return `<div class="space-y-3 pt-4 border-t border-slate-100">${rows.map(l=>`<details class="group/label bg-white/60 rounded-2xl border border-slate-100/50 overflow-hidden" data-label-id="${l.id}">
            <summary class="flex items-center justify-between px-5 py-4 cursor-pointer list-none select-none">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100/50 flex items-center justify-center text-slate-400 group-open/label:bg-purple-50 group-open/label:text-purple-600"><i class="fa-solid fa-tag text-[10px]"></i></div>
                    <span class="text-sm font-bold text-slate-600 tracking-tight">${esc(l.name)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button class="w-7 h-7 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center justify-center" title="Add indicator" data-add-indicator="${l.id}"><i class="fa-solid fa-plus text-[8px]"></i></button>
                    <button class="w-7 h-7 rounded-md bg-slate-50 text-slate-400 hover:text-blue-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Edit" data-edit-label="${l.id}" data-current-name="${esc(l.name)}"><i class="fa-solid fa-pen text-[8px]"></i></button>
                    <button class="w-7 h-7 rounded-md bg-slate-50 text-slate-400 hover:text-rose-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Delete" data-del-label="${l.id}"><i class="fa-solid fa-trash text-[8px]"></i></button>
                    <i class="fa-solid fa-chevron-down text-[8px] text-slate-300 transition-transform group-open/label:rotate-180 ml-1"></i>
                </div>
            </summary>
            <div id="indicators-${l.id}" class="px-5 pb-5 bg-slate-50/30"><div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">${spinner()} Fetching indicators…</div></div>
        </details>`).join('')}</div>`;
  }

  function renderIndicatorsList(rows){
    if(!rows||rows.length===0) return `<div class="p-3 text-center border-t border-slate-100/30"><p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">No indicators.</p></div>`;
    return `<ul class="space-y-2 pt-3 border-t border-slate-100/30">${rows.map(i=>`<li class="px-4 py-3 flex items-center justify-between bg-white/50 rounded-xl border border-slate-100/50 hover:border-blue-200 transition-all" data-indicator-id="${i.id}">
            <div class="flex items-start gap-3"><div class="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 mt-0.5"><i class="fa-solid fa-circle-check text-[10px]"></i></div><div>
                    <div class="text-[13px] font-bold text-slate-700 leading-snug">${esc(i.code?`${i.code}: `:'')}${esc(i.title)}</div>
                    ${i.evidence ? `<div class="text-[10px] font-medium text-slate-400 uppercase tracking-wide mt-1 italic">${esc(i.evidence)}</div>`:''}
                </div></div>
            <div class="flex items-center gap-1">
                <button class="w-7 h-7 rounded-lg bg-white border border-slate-100 text-slate-300 hover:text-blue-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Edit" data-edit-indicator="${i.id}" data-current-code="${esc(i.code)}" data-current-title="${esc(i.title)}" data-current-evidence="${esc(i.evidence)}"><i class="fa-solid fa-pen text-[8px]"></i></button>
                <button class="w-7 h-7 rounded-lg bg-white border border-slate-100 text-slate-300 hover:text-rose-600 flex items-center justify-center ${editMode?'':'hidden'}" title="Delete" data-del-indicator="${i.id}"><i class="fa-solid fa-trash text-[8px]"></i></button>
            </div></li>`).join('')}</ul>`;
  }

  async function loadSections(){
    if(!listEl) return;
    listEl.innerHTML = `<div class="flex items-center justify-center py-20 gap-3 text-slate-400 font-black uppercase tracking-widest text-xs">${spinner(24,24)} <span>Building section map…</span></div>`;
    try{
      const res = await fetch(`${SECT_API}?level_id=${encodeURIComponent(levelId)}&t=${Date.now()}`);
      const json = await res.json();
      if(!json.ok) throw new Error(json.error);
      const rows = Array.isArray(json.data) ? json.data : (json.data && Array.isArray(json.data.items) ? json.data.items : []);
      rows.sort((a,b) => (Number(a.position||a.id) - Number(b.position||b.id)));
      listEl.innerHTML = rows.map(sectionCard).join('') || `<div class="col-span-full py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">No sections configured.</div>`;
    } catch(err) { listEl.innerHTML = `<div class="p-10 text-center text-rose-500 font-black uppercase tracking-widest text-xs">Failure: ${esc(err.message)}</div>`; }
  }

  async function ensureParametersVisible(sectionId, forceOpen){
    const wrap = qs(`#params-${sectionId}`), chev = qs(`[data-chevron="${sectionId}"]`), card = qs(`#section-${sectionId}`);
    if(!wrap) return;
    if(!wrap.classList.contains('hidden') && !forceOpen){ card?.classList.remove('is-open'); wrap.classList.add('hidden'); chev?.classList.remove('rotate-180'); return; }
    wrap.innerHTML = `<div class="p-10 text-center text-[10px] font-black uppercase tracking-widest text-slate-400">${spinner()} Fetching parameters…</div>`;
    try{
        const res = await fetch(`${PARAM_API}?section_id=${encodeURIComponent(sectionId)}&t=${Date.now()}`);
        const json = await res.json();
        wrap.classList.remove('hidden'); chev?.classList.add('rotate-180'); card?.classList.add('is-open');
        wrap.innerHTML = renderParametersList(json.data || []);
    } catch(err) { wrap.innerHTML = `<div class="p-5 text-center text-rose-400 text-[10px] font-black uppercase tracking-widest">Error: ${esc(err.message)}</div>`; }
  }

  async function ensureLabelsVisible(pid){
    const wrap = qs(`#labels-${pid}`); if(!wrap) return;
    try{ const res = await fetch(`${LABEL_API}?parameter_id=${pid}&t=${Date.now()}`); const json = await res.json(); wrap.innerHTML = renderLabelsList(json.data || []); }
    catch(e){ wrap.innerHTML = `<div class="p-4 text-rose-400 text-[10px] font-black uppercase tracking-widest">Error.</div>`; }
  }

  async function ensureIndicatorsVisible(lid){
    const wrap = qs(`#indicators-${lid}`); if(!wrap) return;
    try{ const res = await fetch(`${IND_API}?parameter_label_id=${lid}&t=${Date.now()}`); const json = await res.json(); wrap.innerHTML = renderIndicatorsList(json.data || []); }
    catch(e){ wrap.innerHTML = `<div class="p-4 text-rose-400 text-[10px] font-black uppercase tracking-widest">Error.</div>`; }
  }

  openCreateBtn?.addEventListener('click', ()=>{ sectionIdIn.value=''; sectionNameIn.value=''; show(sectionModal); });
  qs('#sectionCloseX')?.addEventListener('click', ()=>hide(sectionModal));
  qs('#sectionCreateCancel')?.addEventListener('click', ()=>hide(sectionModal));

  sectionForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = new URLSearchParams({ id: sectionIdIn.value, level_id: levelId, name: sectionNameIn.value.trim() });
    const res = await fetch(SECT_API, { method:'POST', body: payload });
    if((await res.json()).ok) { hide(sectionModal); loadSections(); }
  });

  editToggleBtn?.addEventListener('click', ()=>{
    editMode = !editMode;
    editToggleBtn.classList.toggle('bg-blue-600', editMode); editToggleBtn.classList.toggle('text-white', editMode);
    qsa('[data-edit],[data-del],[data-edit-parameter],[data-del-parameter],[data-edit-label],[data-del-label],[data-edit-indicator],[data-del-indicator]').forEach(b => b.classList.toggle('hidden', !editMode));
  });

  listEl?.addEventListener('click', async (e)=>{
    const t = e.target;
    if(t.closest('[data-toggle-parameters]')) ensureParametersVisible(Number(t.closest('[data-toggle-parameters]').dataset.toggleParameters), false);
    if(t.closest('[data-edit]')) { const b = t.closest('[data-edit]'); sectionIdIn.value = b.dataset.edit; sectionNameIn.value = b.dataset.editName; show(sectionModal); }
    if(t.closest('[data-del]')) { const id = t.closest('[data-del]').dataset.del; if(confirm('Delete section?')) { await fetch(`${SECT_API}?id=${id}`, { method:'DELETE' }); loadSections(); } }
    if(t.closest('[data-add-parameter]')){ paramSectionIdIn.value = t.closest('[data-add-parameter]').dataset.addParameter; paramNameIn.value = ''; paramDescIn.value = ''; show(paramModal); }
    if(t.closest('[data-edit-parameter]')){ const b = t.closest('[data-edit-parameter]'), nm = prompt('Edit Title:', b.dataset.currentName); if(nm){ await fetch(PARAM_API, { method:'PATCH', body: JSON.stringify({ id: b.dataset.editParameter, name: nm }) }); ensureParametersVisible(b.closest('[id^="section-"]').id.split('-').pop(), true); } }
    if(t.closest('[data-del-parameter]')){ if(confirm('Delete?')){ await fetch(`${PARAM_API}?id=${t.closest('[data-del-parameter]').dataset.delParameter}`, { method:'DELETE' }); ensureParametersVisible(t.closest('[id^="section-"]').id.split('-').pop(), true); } }
    if(t.closest('[data-add-label]')){ labelParamIdIn.value = t.closest('[data-add-label]').dataset.addLabel; labelNameIn.value = ''; show(labelModal); }
    if(t.closest('[data-edit-label]')){ const b = t.closest('[data-edit-label]'), nm = prompt('Edit:', b.dataset.currentName); if(nm){ await fetch(LABEL_API, { method:'PATCH', body: JSON.stringify({ id: b.dataset.editLabel, name: nm }) }); ensureLabelsVisible(b.closest('[data-parameter-id]').dataset.parameterId); } }
    if(t.closest('[data-del-label]')){ if(confirm('Delete?')){ await fetch(`${LABEL_API}?id=${t.closest('[data-del-label]').dataset.delLabel}`, { method:'DELETE' }); ensureLabelsVisible(t.closest('[data-parameter-id]').dataset.parameterId); } }
    if(t.closest('[data-add-indicator]')){ indLabelId.value = t.closest('[data-add-indicator]').dataset.addIndicator; indCode.value=''; indTitle.value=''; indEvidence.value=''; show(indModal); }
    if(t.closest('[data-edit-indicator]')){ const b = t.closest('[data-edit-indicator]'), nm = prompt('Edit Title:', b.dataset.currentTitle); if(nm){ await fetch(IND_API, { method:'PATCH', body: JSON.stringify({ id: b.dataset.editIndicator, title: nm, code: b.dataset.currentCode, evidence: b.dataset.currentEvidence }) }); ensureIndicatorsVisible(b.closest('[data-label-id]').dataset.labelId); } }
    if(t.closest('[data-del-indicator]')){ if(confirm('Delete?')){ await fetch(`${IND_API}?id=${t.closest('[data-del-indicator]').dataset.delIndicator}`, { method:'DELETE' }); ensureIndicatorsVisible(t.closest('[data-label-id]').dataset.labelId); } }
  });

  paramForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const payload = { section_id: paramSectionIdIn.value, name: paramNameIn.value.trim(), description: paramDescIn.value.trim() }; const res = await fetch(PARAM_API, { method:'POST', body: JSON.stringify(payload) }); if((await res.json()).ok) { hide(paramModal); ensureParametersVisible(payload.section_id, true); } });
  labelForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const payload = { parameter_id: labelParamIdIn.value, name: labelNameIn.value.trim() }; const res = await fetch(LABEL_API, { method:'POST', body: JSON.stringify(payload) }); if((await res.json()).ok) { hide(labelModal); ensureLabelsVisible(payload.parameter_id); } });
  indForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const payload = { parameter_label_id: indLabelId.value, code: indCode.value.trim(), title: indTitle.value.trim(), evidence: indEvidence.value.trim() }; const res = await fetch(IND_API, { method:'POST', body: JSON.stringify(payload) }); if((await res.json()).ok) { hide(indModal); ensureIndicatorsVisible(payload.parameter_label_id); } });

  qsa('[data-close="true"], #paramCloseX, #paramCancel, #labelCloseX, #labelCancel, #indicatorCloseX, #indicatorCancel').forEach(b => b.addEventListener('click', ()=> { hide(sectionModal); hide(paramModal); hide(labelModal); hide(indModal); }));
  listEl?.addEventListener('toggle', (e)=>{ if(!e.target.hasAttribute('open')) return; const pid = e.target.dataset.parameterId, lid = e.target.dataset.labelId; if(pid) ensureLabelsVisible(pid); if(lid) ensureIndicatorsVisible(lid); }, true);
  loadSections();
})();