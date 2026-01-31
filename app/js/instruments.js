// app/js/instruments.js — Modern list + create/update/delete for Instrument page
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

  function open(){ 
    modal?.classList.remove('hidden'); 
    document.body.classList.add('overflow-hidden');
  }
  function close(){ 
    modal?.classList.add('hidden'); 
    document.body.classList.remove('overflow-hidden');
  }
  function resetForm(){ if(form){ form.reset(); if(idIn) idIn.value=''; } }
  function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c])); }

  function chipView(item){
    const name = esc(item.name || '');
    return `
      <a href="javascript:void(0)"
         class="group relative flex flex-col justify-between p-8 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-blue-100/50 hover:border-blue-200 transition-all duration-300"
         data-open="${item.id}">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                <i class="fa-solid fa-file-invoice text-2xl"></i>
            </div>
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-50 text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all duration-300">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </div>
        </div>
        <div>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-2">Accreditation Tool</span>
            <span class="text-2xl font-black text-slate-900 tracking-tight leading-tight block">${name}</span>
        </div>
      </a>`;
  }

  function chipEdit(item){
    const name = esc(item.name || '');
    return `
      <div class="relative flex flex-col justify-between p-8 bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                <i class="fa-solid fa-pen-nib text-2xl"></i>
            </div>
            <div class="flex items-center gap-2">
              <button class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all active:scale-95" title="Edit" data-edit='${JSON.stringify(item)}'>
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all active:scale-95" title="Delete" data-del="${item.id}" data-name="${name}">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
        </div>
        <div>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-2">Editing Mode</span>
            <span class="text-2xl font-black text-slate-900 tracking-tight leading-tight block">${name}</span>
        </div>
      </div>`;
  }

  async function load(){
    try {
      const res = await fetch(`${API}?t=${Date.now()}`);
      const json = await res.json();
      if(!json.ok) { listEl.innerHTML = '<div class="col-span-full py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">Failed to load instruments.</div>'; return; }
      const items = (json.data && json.data.items) || [];
      listEl.innerHTML = items.length
        ? items.map(it => editMode ? chipEdit(it) : chipView(it)).join('')
        : '<div class="col-span-full py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">No instruments yet.</div>';

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
      listEl.innerHTML = '<div class="col-span-full py-20 text-center text-rose-500 font-black uppercase tracking-widest text-xs">Network error.</div>';
    }
  }

  // Events
  openBtn?.addEventListener('click', ()=>{ resetForm(); open(); });
  closeX?.addEventListener('click', close);
  cancelBt?.addEventListener('click', close);
  document.querySelectorAll('.modal-backdrop').forEach(el => el.addEventListener('click', close));
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });

  editTgl?.addEventListener('click', ()=>{ 
    editMode = !editMode; 
    editTgl.classList.toggle('bg-blue-600', editMode);
    editTgl.classList.toggle('text-white', editMode);
    load(); 
  });

  listEl?.addEventListener('click', (e) => {
    const openEl = e.target.closest('[data-open]');
    if (!openEl || editMode) return;
    e.preventDefault();
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

  // init
  load();
})();