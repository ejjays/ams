// app/js/programs.js v10 — Updated for Soft Delete / Archiving
(function(){
  const API = 'programs_api.php';

  const tbody    = document.getElementById('programTbody');
  const createBt = document.getElementById('programCreateBtn');
  const searchIn = document.getElementById('programSearch');

  const modal  = document.getElementById('programCreateModal');
  const mtitle = document.getElementById('programModalTitle');
  const form   = document.getElementById('programCreateForm');
  const cancel = document.getElementById('programCreateCancel');

  const $   = (id) => document.getElementById(id);
  const open= (el) => el && el.classList.remove('hidden');
  const close=(el)=> el && el.classList.add('hidden');
  const esc = (s)  => (s||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));

  function rowView(p){
    const desc = (p.description || '').trim();          // still display read-only column
    const descShort = desc.length > 120 ? (desc.slice(0,117) + '…') : desc;
    const created = (p.created_at || p.created || '').slice(0,10);
    
    // UPDATED: Button is now "Archive" (Amber color) instead of "Delete" (Red)
    return `
      <tr class="bg-white shadow-sm ring-1 ring-slate-200 rounded-lg align-middle">
        <td class="px-5 py-3 font-semibold text-slate-800 rounded-l-lg">${esc(p.code)}</td>
        <td class="px-5 py-3 text-[1.02rem]">${esc(p.name || '')}</td>
        <td class="px-5 py-3 text-slate-600 text-[1.02rem]">${esc(descShort)}</td>
        <td class="px-5 py-3 text-slate-500">${esc(created)}</td>
        <td class="px-5 py-3 rounded-r-lg">
          <a href="javascript:void(0)" class="text-blue-700 hover:underline mr-4" title="Edit"
             data-edit='${JSON.stringify(p)}' onclick="event.stopPropagation()">Edit</a>
          
          <a href="javascript:void(0)" class="text-amber-600 hover:underline" title="Archive"
             data-archive="${p.id}" data-name="${esc(p.code)}" onclick="event.stopPropagation()">
             <i class="fa-solid fa-box-archive mr-1"></i>Archive
          </a>
        </td>
      </tr>`;
  }

  function render(list){
    if (!list || !list.length) {
      tbody.innerHTML = `
        <tr>
          <td class="px-5 py-6 text-slate-500" colspan="5">No active programs found.</td>
        </tr>`;
      return;
    }
    tbody.innerHTML = list.map(rowView).join('');

    // wire Edit
    tbody.querySelectorAll('[data-edit]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const p = JSON.parse(btn.getAttribute('data-edit'));
        mtitle.textContent = 'Edit Program';
        $('program_id').value   = p.id;
        $('program_code').value = p.code || '';
        $('program_name').value = p.name || '';
        open(modal);
      });
    });

    // UPDATED: Wire Archive (formerly Delete)
    tbody.querySelectorAll('[data-archive]').forEach(btn=>{
      btn.addEventListener('click', async ()=>{
        const id = btn.getAttribute('data-archive');
        const name = btn.getAttribute('data-name') || 'this program';
        
        // UPDATED: Message emphasizes archiving, not deleting
        if(!confirm(`Are you sure you want to ARCHIVE ${name}?\n\nIt will be hidden from this list but preserved in the database history.`)) return;
        
        // We still send DELETE method, but backend handles it as a Soft Delete (Update)
        const res = await fetch(`${API}?id=${encodeURIComponent(id)}&t=${Date.now()}`, { method:'DELETE' });
        const json = await res.json();
        
        if(!json.ok) return alert(json.error || 'Archive failed');
        
        // Reload list to remove the archived item
        load(getQ());
      });
    });
  }

  async function load(q=''){
    const url = q ? `${API}?q=${encodeURIComponent(q)}&t=${Date.now()}`
                  : `${API}?t=${Date.now()}`;
    const res = await fetch(url);
    const json = await res.json();
    render(json.ok ? (json.data || []) : []);
    if (!json.ok) console.error(json.error || 'Failed to load programs');
  }

  function getQ(){ return (searchIn?.value || '').trim(); }

  // Create modal (code + name only)
  createBt?.addEventListener('click', ()=>{
    mtitle.textContent = 'Create Program';
    form.reset();
    $('program_id').value = '';
    open(modal);
  });

  // Cancel
  cancel?.addEventListener('click', ()=> close(modal));

  // Save (create/update) — send only code + name
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(form);
    const id   = fd.get('id');
    const code = (fd.get('code')||'').trim();
    const name = (fd.get('name')||'').trim();
    if(!code || !name){ alert('Code and Name are required.'); return; }

    let ok=false;
    // Note: The backend now has "Smart Restore". If you create a code that was previously archived, 
    // the backend will automatically restore it and return success.
    if (id) {
      const payload = { id, code, name };  // no description
      const res = await fetch(API, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const json = await res.json(); ok = json.ok; if(!ok) alert(json.error || 'Update failed');
    } else {
      const res = await fetch(API, { method:'POST', body: fd }); // fd has only code & name
      const json = await res.json(); 
      ok = json.ok; 
      if(!ok) alert(json.error || 'Create failed');
      else if(json.message) alert(json.message); // Show "Program restored" message if applicable
    }
    if (ok) { close(modal); load(getQ()); }
  });

  // Search
  let t = null;
  searchIn?.addEventListener('input', ()=>{
    clearTimeout(t);
    t = setTimeout(()=> load(getQ()), 250);
  });
  searchIn?.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter') { e.preventDefault(); load(getQ()); }
  });

  // Init
  load().catch(console.error);
})();