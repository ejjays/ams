// app/js/instruments.js — list + create/update/delete for Instrument page
(function(){
  const API = 'instruments_api.php';

  const listEl   = document.getElementById('instrumentList');
  const openBtn  = document.getElementById('openCreateInstrument');
  const modal    = document.getElementById('instrumentModal');
  const closeX   = document.getElementById('instrumentCloseX');
  const cancelBt = document.getElementById('instrumentCreateCancel');
  const form     = document.getElementById('instrumentCreateForm');

  const idIn   = document.getElementById('instrument_id');
  const nameIn = document.getElementById('instrument_name');

  const editTgl = document.getElementById('instrumentEditToggleBtn');
  let editMode = false;

  function open(){ modal?.classList.remove('hidden'); }
  function close(){ modal?.classList.add('hidden'); }
  function resetForm(){ if(form){ form.reset(); if(idIn) idIn.value=''; } }
  function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c])); }

  function chipView(item){
    const name = esc(item.name || '');
    return `
      <a href="javascript:void(0)"
         class="program-chip panel px-6 py-5 flex items-center justify-between rounded-xl shadow hover:bg-gray-100"
         data-open="${item.id}">
        <span class="text-lg font-semibold text-slate-800">${name}</span>
        <i class="fa-solid fa-arrow-right text-gray-500 arrow ml-6"></i>
      </a>`;
  }

  function chipEdit(item){
    const name = esc(item.name || '');
    return `
      <div class="program-chip panel px-6 py-5 flex items-center justify-between rounded-xl shadow">
        <span class="text-lg font-semibold text-slate-800">${name}</span>
        <div class="flex items-center gap-2">
          <button class="p-2 rounded-md bg-gray-200 hover:bg-gray-300 text-slate-700" title="Edit" data-edit='${JSON.stringify(item)}'>
            <i class="fa-solid fa-pen"></i>
          </button>
          <button class="p-2 rounded-md bg-gray-200 hover:bg-gray-300 text-slate-700" title="Delete" data-del="${item.id}" data-name="${name}">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>`;
  }

  async function load(){
    try {
      const res = await fetch(`${API}?t=${Date.now()}`);
      const json = await res.json();
      if(!json.ok) { listEl.innerHTML = '<div class="text-gray-500">Failed to load instruments.</div>'; return; }
      const items = (json.data && json.data.items) || [];
      listEl.innerHTML = items.length
        ? items.map(it => editMode ? chipEdit(it) : chipView(it)).join('')
        : '<div class="text-gray-500">No instruments yet.</div>';

      if (editMode) {
        listEl.querySelectorAll('[data-edit]').forEach(btn=>{
          btn.addEventListener('click', ()=>{
            const it = JSON.parse(btn.getAttribute('data-edit'));
            if (idIn) idIn.value = it.id;
            if (nameIn) nameIn.value = it.name || '';
            open();
          });
        });
        listEl.querySelectorAll('[data-del]').forEach(btn=>{
          btn.addEventListener('click', async ()=>{
            const id = btn.getAttribute('data-del');
            const name = btn.getAttribute('data-name') || 'this instrument';
            if(!confirm(`Delete ${name}?`)) return;
            const res = await fetch(`${API}?id=${encodeURIComponent(id)}&t=${Date.now()}`, { method:'DELETE' });
            const json = await res.json();
            if(!json.ok) { alert(json.error || 'Delete failed'); return; }
            load();
          });
        });
      }
    } catch (e) {
      console.error(e);
      listEl.innerHTML = '<div class="text-gray-500">Network error.</div>';
    }
  }

  // Events
  openBtn?.addEventListener('click', ()=>{ resetForm(); open(); });
  closeX?.addEventListener('click', close);
  cancelBt?.addEventListener('click', close);
  document.querySelectorAll('.modal-backdrop').forEach(el => el.addEventListener('click', close));
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });

  editTgl?.addEventListener('click', ()=>{ editMode = !editMode; load(); });
  // >>> Navigate to level.php when clicking the arrow or double-clicking the chip
  listEl?.addEventListener('click', (e) => {
    const openEl = e.target.closest('[data-open]');
    if (!openEl) return;
    e.preventDefault(); // avoid default <a href> behavior
    const id = openEl.getAttribute('data-open');
    if (id) window.location.href = 'level.php?instrument_id=' + encodeURIComponent(id);
  });

  listEl?.addEventListener('dblclick', (e) => {
    const openEl = e.target.closest('[data-open]');
    if (!openEl) return;
    const id = openEl.getAttribute('data-open');
    if (id) window.location.href = 'level.php?instrument_id=' + encodeURIComponent(id);
  });

  form?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    try {
      const fd = new FormData(form);
      const res = await fetch(API, { method: 'POST', body: fd });
      const json = await res.json();
      if(!json.ok) { alert(json.error || 'Save failed'); return; }
      close(); resetForm(); load();
    } catch(err) {
      alert('Network error'); console.error(err);
    }
  });

  if (typeof tagBtn !== 'undefined' && tagBtn) {
    tagBtn.addEventListener('click', ()=>{
    });
  }

  // init
  load();
})();