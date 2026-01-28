
// ======= Generic Form Modal =======
async function showFormModal({title='Edit', fields=[], submitText='Save'}){
  return new Promise((resolve,reject)=>{
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 z-50 flex items-center justify-center';
    overlay.style.background = 'rgba(0,0,0,0.4)';
    const modal = document.createElement('div');
    modal.className = 'bg-white w-[420px] max-w-[90vw] rounded-2xl shadow-xl';
    modal.innerHTML = `
      <div class="p-5 border-b flex items-center justify-between">
        <div class="text-lg font-semibold">${title}</div>
        <button class="p-1" data-x><i class="fa-solid fa-xmark"></i></button>
      </div>
      <form class="p-5 space-y-4">
        ${fields.map(f=>`
          <div>
            <label class="block text-sm font-medium mb-1">${f.label}${f.optional?' <span class="text-gray-400">(optional)</span>':''}</label>
            ${f.type==='textarea'
              ? `<textarea name="${f.name}" rows="4" class="w-full rounded-md border px-3 py-2" placeholder="${f.placeholder||''}">${f.value??''}</textarea>`
              : `<input name="${f.name}" type="${f.type||'text'}" class="w-full rounded-md border px-3 py-2" placeholder="${f.placeholder||''}" value="${f.value??''}">`
            }
          </div>
        `).join('')}
        <div class="flex items-center justify-end gap-3 pt-2">
          <button type="button" data-cancel class="px-4 py-2 rounded-md bg-gray-200 text-gray-700">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white">${submitText}</button>
        </div>
      </form>
    `;
    overlay.appendChild(modal);
    function close(){ overlay.remove(); }
    overlay.addEventListener('click', (e)=>{ if(e.target===overlay) close(); });
    modal.querySelector('[data-x]').addEventListener('click', close);
    modal.querySelector('[data-cancel]').addEventListener('click', close);
    modal.querySelector('form').addEventListener('submit', (e)=>{
      e.preventDefault();
      const data = Object.fromEntries(new FormData(e.currentTarget).entries());
      close();
      resolve(data);
    });
    document.body.appendChild(overlay);
  });
}

// ---- Edit-mode CSS gate ----
(function(){
  if (!document.getElementById('edit-mode-style')) {
    const s = document.createElement('style');
    s.id = 'edit-mode-style';
    s.textContent = `[data-edit-mode="0"] [data-edit-only]{display:none !important;}`;
    document.head.appendChild(s);
  }
  if (!document.body.hasAttribute('data-edit-mode')) {
    document.body.setAttribute('data-edit-mode','0');
  }
})();
// app/js/sections.js — FIXED (v10.1)
// - Reliable expand/collapse for Parameter and Label <details>
// - Loads Labels and Indicators on native <details> 'toggle'
// - No reliance on missing data-toggle-* attributes
// - Safer event delegation; no accidental preventDefault on <summary>

(function(){
  "use strict";

  // ---- Endpoints ----
  const SECT_API  = 'sections_api.php';
  const PARAM_API = 'parameters_api.php';
  const LABEL_API = 'parameter_labels_api.php';
  const IND_API   = 'indicator_labels_api.php';

  // ---- Helpers ----
  const qs  = (s,el=document)=>el.querySelector(s);
  const qsa = (s,el=document)=>Array.from(el.querySelectorAll(s));
  const esc = (s)=>String(s==null?'':s).replace(/[&<>\"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const levelId = Number(new URLSearchParams(location.search).get('level_id')||0);

  // ---- Elements ----
  const listEl = qs('#sectionList');
  const editToggleBtn = qs('#sectionEditToggleBtn');

  // Section modal
  const sectionModal  = qs('#sectionModal');
  const sectionCloseX = qs('#sectionCloseX');
  const sectionCancel = qs('#sectionCreateCancel');
  const sectionForm   = qs('#sectionCreateForm');
  const sectionIdIn   = qs('#section_id');
  const sectionNameIn = qs('#section_name');
  const sectionDescIn = qs('#section_desc');
  const openCreateBtn = qs('#openCreateSection');

  // Parameter modal
  const paramModal       = qs('#parameterModal');
  const paramCloseX      = qs('#paramCloseX');
  const paramCancel      = qs('#paramCancel');
  const paramForm        = qs('#paramForm');
  const paramSectionIdIn = qs('#param_section_id');
  const paramNameIn      = qs('#param_name');
  const paramDescIn      = qs('#param_description');

  // Label modal
  const labelModal     = qs('#labelModal');
  const labelCloseX    = qs('#labelCloseX');
  const labelCancel    = qs('#labelCancel');
  const labelForm      = qs('#labelForm');
  const labelParamIdIn = qs('#label_param_id');
  const labelNameIn    = qs('#label_name');
  const labelDescIn    = qs('#label_description');

  // Indicator modal
  const indModal   = qs('#indicatorModal');
  const indCloseX  = qs('#indicatorCloseX');
  const indCancel  = qs('#indicatorCancel');
  const indForm    = qs('#indicatorForm');
  const indLabelId = qs('#indicator_label_id');
  const indCode    = qs('#indicator_code');
  const indTitle   = qs('#indicator_title');
  const indEvidence= qs('#indicator_evidence');

  let editMode = false;
  function applyEditModeToDOM(){
    const editBtns = qsa('[data-edit]');
    const delBtns  = qsa('[data-del]');
    [...editBtns, ...delBtns].forEach(btn=>{
      if(!btn || !btn.classList) return;
      if(editMode){ btn.classList.remove('hidden'); }
      else { btn.classList.add('hidden'); }
    });
  }

  function show(el){ if(el) el.classList.remove('hidden'); }
  function hide(el){ if(el) el.classList.add('hidden'); }

  // ---- Renderers ----
  function sectionCard(s){
    const sid = Number(s.id);
    return `
      <div class="bg-white border rounded-lg shadow-none" id="section-${sid}">
        <div class="flex items-center justify-between p-4 bg-white rounded-t">
          <div class="font-medium text-slate-800">${esc(s.name)}</div>
          <div class="flex items-center gap-2">
            <button class="rounded bg-blue-600 text-white px-2 py-1 text-sm" title="Add parameter" data-add-parameter="${sid}"><i class="fa-solid fa-plus"></i></button>
            <button class="rounded border px-2 py-1 text-sm" title="Show parameters" data-toggle-parameters="${sid}"><i class="fa-solid fa-chevron-down" data-chevron="${sid}"></i></button>
            <button class="rounded border px-2 py-1 text-sm ${editMode?'':'hidden'}" data-edit="${sid}" data-edit-name="${esc(s.name)}" data-edit-desc="${esc(s.description||'')}" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <button class="rounded border px-2 py-1 text-sm ${editMode?'':'hidden'}" data-del="${sid}" title="Delete"><i class="fa-solid fa-trash"></i></button>
          </div>
        </div>
        ${s.description ? `<div class="px-4 py-2 text-sm text-slate-700">${esc(s.description)}</div>`:''}
        <div id="params-${sid}" class="hidden border-t"></div>
      </div>`;
  }

  function renderParametersList(rows){
  if(!rows||rows.length===0){
    return `<div class="rounded border bg-white p-2"><div class="p-3 text-sm text-gray-500">No parameters yet.</div></div>`;
  }
  return `<div class="rounded border bg-white p-2 space-y-2">${
    rows.map(r=>`
      <details class="group" data-parameter-id="${r.id}">
        <summary class="flex items-center justify-between cursor-pointer list-none px-3 py-2  rounded bg-white  rounded">
          <span class="font-medium text-gray-800">${esc(r.name)}</span>
          <div class="flex items-center gap-1">
            <button class="inline-flex items-center justify-center w-7 h-7 rounded bg-blue-600 text-white text-xs" title="Add label" data-add-label="${r.id}"><i class="fa-solid fa-plus"></i></button>
            <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-sm" title="Edit Parameter" data-edit-only data-edit-parameter="${r.id}"><i class="fa-solid fa-pen"></i></button>
            <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-sm" title="Delete Parameter" data-edit-only data-del-parameter="${r.id}"><i class="fa-solid fa-trash"></i></button>
            <i class="fa-solid fa-chevron-down flex-none w-4 h-4 transition-transform group-open:rotate-180"></i>
          </div>
        </summary>
        ${r.description ? `<div class="px-3 pb-2 text-sm text-gray-700">${esc(r.description)}</div>` : ``}
        <div id="labels-${r.id}" class="px-3 pb-3"><div class="text-sm text-gray-500">Expand to load labels…</div></div>
      </details>`).join('')
  }</div>`;
}

  function renderLabelsList(rows){
  if(!rows||rows.length===0){
    return `<div class="rounded border bg-white p-2"><div class="p-3 text-sm text-gray-500">No labels yet.</div></div>`;
  }
  return `<div class="rounded border bg-white p-2 space-y-2">${
    rows.map(l=>`
      <details class="group" data-label-id="${l.id}">
        <summary class="flex items-center justify-between px-3 py-2 cursor-pointer list-none  rounded bg-white hover:bg-white rounded">
          <span class="font-medium">${esc(l.name)}</span>
          <div class="flex items-center gap-1">
            <button class="inline-flex items-center justify-center w-7 h-7 rounded bg-blue-600 text-white text-xs" title="Add indicator" data-add-indicator="${l.id}"><i class="fa-solid fa-plus"></i></button>
            <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-sm" title="Edit Label" data-edit-only data-edit-label="${l.id}"><i class="fa-solid fa-pen"></i></button>
            <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-sm" title="Delete Label" data-edit-only data-del-label="${l.id}"><i class="fa-solid fa-trash"></i></button>
            <i class="fa-solid fa-chevron-down flex-none w-4 h-4 transition-transform group-open:rotate-180"></i>
          </div>
        </summary>
        ${l.description ? `<div class="px-3 pb-2 text-sm text-gray-700">${esc(l.description)}</div>` : ``}
        <div id="indicators-${l.id}" class="px-3 pb-3"><div class="text-sm text-gray-500">Expand to load indicators…</div></div>
      </details>`).join('')
  }</div>`;
}

  function renderIndicatorsList(rows){
  if(!rows||rows.length===0){
    return `<div class="rounded border bg-white p-2"><div class="p-3 text-sm text-gray-500">No indicators yet.</div></div>`;
  }
  return `<ul class="rounded border bg-white p-2 space-y-2">${
    rows.map(i=>`
      <li class="px-3 py-2 flex items-center justify-between bg-gray-50  rounded" data-indicator-id="${i.id}">
        <div>
          <div class="font-medium">${esc(i.code?`${i.code}: `:'')}${esc(i.title||'')}</div>
          ${i.evidence ? `<div class="text-sm text-gray-700">${esc(i.evidence)}</div>`:''}
        </div>
        <div class="flex items-center gap-1">
          <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-xs" title="Edit Indicator" data-edit-only data-edit-indicator="${i.id}"><i class="fa-solid fa-pen"></i></button>
          <button class="inline-flex items-center justify-center w-7 h-7 rounded border text-xs" title="Delete Indicator" data-edit-only data-del-indicator="${i.id}"><i class="fa-solid fa-trash"></i></button>
        </div>
      </li>`).join('')
  }</ul>`;
}

 // ---- Loaders ----
async function loadSections(){
  if(!listEl) return;
  listEl.innerHTML = `<div class="p-3 text-sm text-gray-500">Loading…</div>`;
  try{
    const res = await fetch(`${SECT_API}?level_id=${encodeURIComponent(levelId)}&t=${Date.now()}`);
    const json = await res.json();
    if(!json.ok) throw new Error(json.error||'Failed to load sections');

    // ORIHINAL na extraction logic — huwag aalisin
    const rows = Array.isArray(json.data)
      ? json.data
      : (json.data && (Array.isArray(json.data.items)
          ? json.data.items
          : (Array.isArray(json.data.rows) ? json.data.rows : [])));

    // Old → New (bagong section sa dulo), defensive
    rows.sort((a, b) => {
      const axRaw = (a && a.position != null) ? a.position : (a && a.id != null ? a.id : 0);
      const bxRaw = (b && b.position != null) ? b.position : (b && b.id != null ? b.id : 0);
      const ax = Number.parseInt(axRaw, 10) || 0;
      const bx = Number.parseInt(bxRaw, 10) || 0;
      return ax - bx;
    });

    listEl.innerHTML = rows.map(sectionCard).join('');
  }catch(err){
    listEl.innerHTML = `<div class="p-3 text-sm text-red-600">${esc(err.message||'Failed to load')}</div>`;
  }
}


  async function loadParameters(sectionId){
    const res = await fetch(`${PARAM_API}?section_id=${encodeURIComponent(sectionId)}&t=${Date.now()}`);
    const json = await res.json();
    if(!json.ok) throw new Error(json.error||'Failed to load parameters');
    return Array.isArray(json.data)? json.data : [];
  }
  async function loadLabels(parameterId){
    const res = await fetch(`${LABEL_API}?parameter_id=${encodeURIComponent(parameterId)}&t=${Date.now()}`);
    const json = await res.json();
    if(!json.ok) throw new Error(json.error||'Failed to load labels');
    return Array.isArray(json.data)? json.data : [];
  }
  async function loadIndicators(labelId){
    const res = await fetch(`${IND_API}?parameter_label_id=${encodeURIComponent(labelId)}&t=${Date.now()}`);
    const json = await res.json();
    if(!json.ok) throw new Error(json.error||'Failed to load indicators');
    return Array.isArray(json.data)? json.data : [];
  }

  // ---- Expand helpers ----
  async function ensureParametersVisible(sectionId, forceOpen){
    const wrap = qs(`#params-${sectionId}`);
    const chev = qs(`[data-chevron="${sectionId}"]`);
    if(!wrap) return;
    const hidden = wrap.classList.contains('hidden');
    const card = qs(`#section-${sectionId}`);
    if(!hidden && !forceOpen){
      card && card.classList.remove('is-open');
      wrap.classList.add('hidden'); chev&&chev.classList.remove('rotate-180'); return;
    }
    wrap.innerHTML = `<div class="p-3 text-sm text-gray-500">Loading…</div>`;
    try{
      const rows = await loadParameters(sectionId);
      wrap.classList.remove('hidden');
      chev&&chev.classList.add('rotate-180');
      const card2 = qs(`#section-${sectionId}`);
      card2 && card2.classList.add('is-open');
      wrap.innerHTML = `<div class="border-l-0">${renderParametersList(rows)}</div>`;
    }catch(err){
      wrap.innerHTML = `<div class="p-3 text-sm text-red-600">${esc(err.message||'Failed to load')}</div>`;
    }
  }

  async function ensureLabelsVisible(parameterId){
    const wrap = qs(`#labels-${parameterId}`);
    if(!wrap) return;
    wrap.innerHTML = `<div class="p-3 text-sm text-gray-500">Loading…</div>`;
    try{
      wrap.innerHTML = renderLabelsList(await loadLabels(parameterId));
    }catch(err){
      wrap.innerHTML = `<div class="p-3 text-sm text-red-600">${esc(err.message||'Failed to load')}</div>`;
    }
  }

  async function ensureIndicatorsVisible(labelId){
    const wrap = qs(`#indicators-${labelId}`);
    if(!wrap) return;
    wrap.innerHTML = `<div class="p-3 text-sm text-gray-500">Loading…</div>`;
    try{
      wrap.innerHTML = renderIndicatorsList(await loadIndicators(labelId));
    }catch(err){
      wrap.innerHTML = `<div class="p-3 text-sm text-red-600">${esc(err.message||'Failed to load')}</div>`;
    }
  }

  // ---- Section modal events ----
  openCreateBtn&&openCreateBtn.addEventListener('click', ()=>{
    sectionIdIn && (sectionIdIn.value='');
    sectionNameIn && (sectionNameIn.value='');
    sectionDescIn && (sectionDescIn.value='');
    show(sectionModal);
    setTimeout(()=>sectionNameIn&&sectionNameIn.focus(),0);
  });
  sectionCloseX&&sectionCloseX.addEventListener('click', ()=>hide(sectionModal));
  sectionCancel&&sectionCancel.addEventListener('click', ()=>hide(sectionModal));
  sectionModal&&sectionModal.addEventListener('click', (e)=>{ if(e.target===sectionModal) hide(sectionModal); });

  sectionForm&&sectionForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = {
      id: sectionIdIn&&sectionIdIn.value? Number(sectionIdIn.value):null,
      level_id: levelId,
      name: (sectionNameIn&&sectionNameIn.value||'').trim(),
      description: (sectionDescIn&&sectionDescIn.value||'').trim()
    };
    if(!payload.level_id || !payload.name){ alert('Level and name are required'); return; }
    const form = new URLSearchParams(); Object.entries(payload).forEach(([k,v])=>form.append(k,String(v==null?'':v)));
    const res = await fetch(SECT_API, { method:'POST', body: form });
    const json = await res.json();
    if(!json.ok) { alert(json.error||'Save failed'); return; }
    // on success
hide(sectionModal);
await loadSections();        // <— refresh list
applyEditModeToDOM();

  });
  // ---- Edit mode toggle (topbar pencil) ----
  editToggleBtn && editToggleBtn.addEventListener('click', ()=>{
    editMode = !editMode;
    // highlight the button
    if (editToggleBtn.classList) {
      editToggleBtn.classList.toggle('ring-2', editMode);
      editToggleBtn.classList.toggle('ring-offset-2', editMode);
    }
    // Re-render section list so buttons reflect editMode
    applyEditModeToDOM();
  });


  // ---- List click actions (no impact on summary toggling) ----
  listEl&&listEl.addEventListener('click', async (e)=>{
    // Add Parameter
    const addParamBtn = e.target.closest('[data-add-parameter]');
    if(addParamBtn){
      const sid = Number(addParamBtn.getAttribute('data-add-parameter')||'0');
      paramSectionIdIn && (paramSectionIdIn.value = String(sid));
      paramNameIn && (paramNameIn.value = '');
      paramDescIn && (paramDescIn.value = '');
      show(paramModal);
      setTimeout(()=>paramNameIn&&paramNameIn.focus(),0);
      return;
    }

    
    // Edit Section
    const editBtn = e.target.closest('[data-edit]');
    if(editBtn){
      const sid = Number(editBtn.getAttribute('data-edit')||'0');
      const nm  = editBtn.getAttribute('data-edit-name') || '';
      const dc  = editBtn.getAttribute('data-edit-desc') || '';
      if(sectionIdIn) sectionIdIn.value = String(sid);
      if(sectionNameIn) sectionNameIn.value = nm;
      if(sectionDescIn) sectionDescIn.value = dc;
      show(sectionModal);
      setTimeout(()=>sectionNameIn&&sectionNameIn.focus(),0);
      return;
    }

    // Delete Section
    const delBtn = e.target.closest('[data-del]');
    if(delBtn){
      const sid = Number(delBtn.getAttribute('data-del')||'0');
      if(!sid) return;
      if(!confirm('Delete this section? This cannot be undone.')) return;
      try{
        const res = await fetch(`${SECT_API}?id=${encodeURIComponent(sid)}`, { method:'DELETE' });
        const json = await res.json();
        if(!json.ok){ alert(json.error||'Delete failed'); return; }
        loadSections();
      }catch(err){ alert(err.message||'Delete failed'); }
      return;
    }
// Toggle parameters panel for a section
    const toggleBtn = e.target.closest('[data-toggle-parameters]');
    if(toggleBtn){
      const sid = Number(toggleBtn.getAttribute('data-toggle-parameters')||'0');
      await ensureParametersVisible(sid, false);
      return;
    }

    // Add Label
    const addLabelBtn = e.target.closest('[data-add-label]');
    if(addLabelBtn){
      const pid = Number(addLabelBtn.getAttribute('data-add-label')||'0');
      labelParamIdIn && (labelParamIdIn.value = String(pid));
      labelNameIn && (labelNameIn.value = '');
      labelDescIn && (labelDescIn.value = '');
      show(labelModal);
      setTimeout(()=>labelNameIn&&labelNameIn.focus(),0);
      return;
    }

    // Add Indicator
    const addI = e.target.closest('[data-add-indicator]');
    if(addI){
      const lid = Number(addI.getAttribute('data-add-indicator')||'0');
      indLabelId && (indLabelId.value = String(lid));
      indCode && (indCode.value = '');
      indTitle && (indTitle.value = '');
      indEvidence && (indEvidence.value = '');
      show(indModal);
      setTimeout(()=>indTitle&&indTitle.focus(),0);
      return;
    }
  });

  
  // ---- Edit/Delete in <summary> (prevent toggle) ----
  listEl&&listEl.addEventListener('click', async (e)=>{
    const btn = e.target.closest('[data-edit-parameter],[data-del-parameter],[data-edit-label],[data-del-label],[data-edit-indicator],[data-del-indicator]');
    if(!btn) return;
    e.preventDefault();
    e.stopPropagation();
    if(btn.hasAttribute('data-edit-parameter')){
      const id = Number(btn.getAttribute('data-edit-parameter')||'0');
      const form = await showFormModal({ title: 'Section Parameter', fields:[{label:'Name', name:'name', value: btn.closest('summary')?.querySelector('span')?.textContent?.trim()||''}], submitText:'Save' });
      const { name } = form;
      const description = '';
      try{
        const res = await fetch('parameters_api.php', { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id, name, description }) });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const card = btn.closest('[id^="section-"]');
        if(card){
          const sid = Number(card.id.split('-').pop());
          const wrap = document.querySelector('#params-'+sid);
          wrap && (wrap.innerHTML = '<div class="p-3 text-sm text-gray-500">Refreshing…</div>');
          const rows = await loadParameters(sid);
          wrap && (wrap.innerHTML = '<div class="border-l-0">'+renderParametersList(rows)+'</div>');
        }
      }catch(err){ alert(err.message); }
      return;
    }
    if(btn.hasAttribute('data-del-parameter')){
      const id = Number(btn.getAttribute('data-del-parameter')||'0');
      if(!confirm('Delete this parameter? This will remove its labels & indicators.')) return;
      try{
        const res = await fetch('parameters_api.php?id='+encodeURIComponent(id), { method:'DELETE' });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const card = btn.closest('[id^="section-"]');
        if(card){
          const sid = Number(card.id.split('-').pop());
          const wrap = document.querySelector('#params-'+sid);
          wrap && (wrap.innerHTML = '<div class="p-3 text-sm text-gray-500">Refreshing…</div>');
          const rows = await loadParameters(sid);
          wrap && (wrap.innerHTML = '<div class="border-l-0">'+renderParametersList(rows)+'</div>');
        }
      }catch(err){ alert(err.message); }
      return;
    }
    if(btn.hasAttribute('data-edit-label')){
      const id = Number(btn.getAttribute('data-edit-label')||'0');
      const form = await showFormModal({ title: 'Parameter Label', fields:[{label:'Name', name:'name', value: btn.closest('summary')?.querySelector('span')?.textContent?.trim()||''}], submitText:'Save' });
      const { name } = form;
      const description = '';
      try{
        const res = await fetch('parameter_labels_api.php', { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id, name, description }) });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const pd = btn.closest('[data-parameter-id]');
        if(pd){
          const pid = Number(pd.getAttribute('data-parameter-id'));
          await ensureLabelsVisible(pid);
        }
      }catch(err){ alert(err.message); }
      return;
    }
    if(btn.hasAttribute('data-del-label')){
      const id = Number(btn.getAttribute('data-del-label')||'0');
      if(!confirm('Delete this label? This will remove its indicators.')) return;
      try{
        const res = await fetch('parameter_labels_api.php?id='+encodeURIComponent(id), { method:'DELETE' });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const pd = btn.closest('[data-parameter-id]');
        if(pd){
          const pid = Number(pd.getAttribute('data-parameter-id'));
          await ensureLabelsVisible(pid);
        }
      }catch(err){ alert(err.message); }
      return;
    }
    if(btn.hasAttribute('data-edit-indicator')){
      const id = Number(btn.getAttribute('data-edit-indicator')||'0');
      const form = await showFormModal({ title: 'Indicator', fields:[{label:'Code', name:'code', optional:true, value: ''},{label:'Title', name:'title', value: btn.closest('li')?.querySelector('.font-medium')?.textContent?.trim()||''},{label:'Evidence', name:'evidence', type:'textarea', optional:true, value: btn.closest('li')?.querySelector('.text-sm.text-gray-700')?.textContent?.trim()||''}], submitText:'Save' });
      const { code, title, evidence } = form;
      try{
        const res = await fetch('indicator_labels_api.php', { method:'PATCH', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id, code, title, evidence }) });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const ld = btn.closest('[data-label-id]');
        if(ld){
          const lid = Number(ld.getAttribute('data-label-id'));
          await ensureIndicatorsVisible(lid);
        }
      }catch(err){ alert(err.message); }
      return;
    }
    if(btn.hasAttribute('data-del-indicator')){
      const id = Number(btn.getAttribute('data-del-indicator')||'0');
      if(!confirm('Delete this indicator?')) return;
      try{
        const res = await fetch('indicator_labels_api.php?id='+encodeURIComponent(id), { method:'DELETE' });
        const json = await res.json();
        if(!json.ok) throw new Error(json.error||'Failed');
        const ld = btn.closest('[data-label-id]');
        if(ld){
          const lid = Number(ld.getAttribute('data-label-id'));
          await ensureIndicatorsVisible(lid);
        }
      }catch(err){ alert(err.message); }
      return;
    }
  });

// ---- Hook top-bar Edit button to toggle edit mode ----
(function(){
  function setEditMode(on){
    document.body.setAttribute('data-edit-mode', on ? '1' : '0');
  }
  window.setEditMode = setEditMode;
  function findTopbarEditButton(){
    const candidates = Array.from(document.querySelectorAll('button, a')).filter(el=>{
      const i = el.querySelector('i.fa-pen, i.fa-pen-to-square');
      if(!i) return false;
      const r = el.getBoundingClientRect();
      return r.top < 180 && r.left > 600;
    });
    return candidates[0] || null;
  }
  function attach(){
    const btn = findTopbarEditButton();
    if(!btn) return;
    if(btn.__editHooked) return;
    btn.__editHooked = true;
    btn.addEventListener('click', (e)=>{
      const on = document.body.getAttribute('data-edit-mode') !== '1';
      setEditMode(on);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', attach);
  } else {
    attach();
    setTimeout(attach, 500);
  }
})();
// ---- Native <details> toggle listeners (this fixes dropdown issues) ----
  listEl&&listEl.addEventListener('toggle', (e)=>{
    const det = e.target;
    if(!(det instanceof HTMLElement)) return;
    if(!det.hasAttribute('open')) return; // only react when opening
    const pid = det.getAttribute('data-parameter-id');
    if(pid) ensureLabelsVisible(Number(pid));
  }, true);

  listEl&&listEl.addEventListener('toggle', (e)=>{
    const det = e.target;
    if(!(det instanceof HTMLElement)) return;
    if(!det.hasAttribute('open')) return;
    const lid = det.getAttribute('data-label-id');
    if(lid) ensureIndicatorsVisible(Number(lid));
  }, true);

  // ---- Parameter modal ----
  paramCloseX&&paramCloseX.addEventListener('click', ()=>hide(paramModal));
  paramCancel&&paramCancel.addEventListener('click', ()=>hide(paramModal));
  paramModal&&paramModal.addEventListener('click', (e)=>{ if(e.target===paramModal) hide(paramModal); });
  paramForm&&paramForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = {
      section_id: Number(paramSectionIdIn&&paramSectionIdIn.value||0),
      name: (paramNameIn&&paramNameIn.value||'').trim(),
      description: (paramDescIn&&paramDescIn.value||'').trim() || null
    };
    if(!payload.section_id || !payload.name){ alert('Section and name are required'); return; }
    const res = await fetch(PARAM_API, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const json = await res.json();
    if(!json.ok){ alert(json.error||'Failed to create'); return; }
    hide(paramModal);
    ensureParametersVisible(payload.section_id, true);
  });

  // ---- Label modal ----
  labelCloseX&&labelCloseX.addEventListener('click', ()=>hide(labelModal));
  labelCancel&&labelCancel.addEventListener('click', ()=>hide(labelModal));
  labelModal&&labelModal.addEventListener('click', (e)=>{ if(e.target===labelModal) hide(labelModal); });
  labelForm&&labelForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = {
      parameter_id: Number(labelParamIdIn&&labelParamIdIn.value||0),
      name: (labelNameIn&&labelNameIn.value||'').trim(),
      description: (labelDescIn&&labelDescIn.value||'').trim() || null
    };
    if(!payload.parameter_id || !payload.name){ alert('Parameter and name are required'); return; }
    const res = await fetch(LABEL_API, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const json = await res.json();
    if(!json.ok){ alert(json.error||'Failed to create'); return; }
    hide(labelModal);
    ensureLabelsVisible(payload.parameter_id);
  });

  // ---- Indicator modal ----
  indCloseX&&indCloseX.addEventListener('click', ()=>hide(indModal));
  indCancel&&indCancel.addEventListener('click', ()=>hide(indModal));
  indModal&&indModal.addEventListener('click', (e)=>{ if(e.target===indModal) hide(indModal); });
  indForm&&indForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const payload = {
      parameter_label_id: Number(indLabelId&&indLabelId.value||0),
      code: (indCode&&indCode.value||'').trim(),
      title: (indTitle&&indTitle.value||'').trim(),
      evidence: (indEvidence&&indEvidence.value||'').trim() || null
    };
    if(!payload.parameter_label_id || !payload.title){ alert('Label and title are required'); return; }
    const res = await fetch(IND_API, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const json = await res.json();
    if(!json.ok){ alert(json.error||'Failed to create'); return; }
    hide(indModal);
    // After creation, ensure label's indicators are visible and refreshed
    const cont = qs(`#indicators-${payload.parameter_label_id}`);
    if(cont){ await ensureIndicatorsVisible(payload.parameter_label_id); }
  });

  // ---- Init ----
  if(!levelId){ console.warn('Missing level_id'); }
  loadSections();
  setTimeout(()=>applyEditModeToDOM(),0);
})();
