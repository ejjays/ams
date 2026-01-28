// app/js/visit.js — Visit Management UI (Delete button removed)
(function(){
  const API = 'visit_api.php';

  const searchIn = document.getElementById('visitSearch');
  const statusSel= document.getElementById('visitStatus');
  const fromIn   = document.getElementById('visitFrom');
  const toIn     = document.getElementById('visitTo');
  const applyBtn = document.getElementById('visitApply');
  const addBtn   = document.getElementById('visitAddBtn');
  const exportBtn= document.getElementById('visitExportBtn');

  const tableBody= document.getElementById('visitTbody');
  const resultTxt= document.getElementById('visitResultText');
  const prevBtn  = document.getElementById('visitPrev');
  const nextBtn  = document.getElementById('visitNext');
  const pageLbl  = document.getElementById('visitPageLabel');

  const modal    = document.getElementById('visitModal');
  const modalTitle = document.getElementById('visitModalTitle');
  const form     = document.getElementById('visitForm');
  const cancelBtn= document.getElementById('visitCancel');

  let page = 1, pages = 1, limit = 10;

  // Safe text
  function esc(s){
    return (s==null?'':String(s)).replace(/[&<>"]/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
  }

  // Light/pastel status pill
  function badge(st){
    const key = String(st||'').toLowerCase();
    const labelMap = { planned:'Planned', ongoing:'Ongoing', completed:'Completed', cancelled:'Cancelled' };
    const clsMap = {
      planned:   'bg-sky-100     text-sky-700     ring-sky-200',
      ongoing:   'bg-amber-100   text-amber-700   ring-amber-200',
      completed: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
      cancelled: 'bg-rose-100    text-rose-700    ring-rose-200',
    };
    const label = labelMap[key] || (st || '');
    const cls   = clsMap[key] || 'bg-gray-100 text-gray-700 ring-gray-200';
    return `<span class="px-2.5 py-1 rounded-full text-xs font-medium ring-1 ${cls}">${esc(label)}</span>`;
  }

  async function list(){
    const params = new URLSearchParams({
      action:'list',
      q:  searchIn?.value?.trim()||'',
      status: statusSel?.value||'all',
      from: fromIn?.value||'',
      to:   toIn?.value||'',
      page: String(page),
      limit:String(limit)
    });
    const res = await fetch(API + '?' + params.toString(), {credentials:'same-origin'});
    if (!res.ok) throw new Error('Failed to load');
    const j = await res.json();
    if (!j.ok) throw new Error(j.error||'Error');

    const {items,total,page:pg,pages:pgs} = j.data;
    page = pg; pages = Math.max(1, pgs||1);

    tableBody.innerHTML = (items||[]).map(r=>`
      <tr class="border-b hover:bg-slate-50">
        <td class="px-4 py-2">${esc(r.team||'')}</td>
        <td class="px-4 py-2 whitespace-nowrap">${esc(r.visit_date||'')}</td>
        <td class="px-4 py-2">${esc(r.purpose||'')}</td>
        <td class="px-4 py-2">${badge(r.status)}</td>
        <td class="px-4 py-2 truncate max-w-[360px]">${esc(r.notes||'')}</td>
        <td class="px-4 py-2 text-sm">
          <button class="text-blue-700 hover:underline" data-edit="${r.id}">Edit</button>
        </td>
      </tr>
    `).join('') || `<tr><td colspan="6" class="px-4 py-6 text-slate-500 text-sm">No visits found.</td></tr>`;

    resultTxt.textContent = total + (total===1?' result':' results');
    pageLbl.textContent = String(page);
    prevBtn.disabled = page<=1;
    nextBtn.disabled = page>=pages;
  }

  tableBody?.addEventListener('click', (e)=>{
    const t = e.target;
    if (t.dataset.edit){
      openModal(parseInt(t.dataset.edit,10));
    }
    // Delete logic removed here
  });

  function openModal(id){
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    form.reset();
    form.dataset.id = id?String(id):'';
    modalTitle.textContent = id ? 'Edit Visit' : 'Add Visit';

    if (id){
      // Pull from list endpoint (simpler reuse)
      fetch(API + '?' + new URLSearchParams({action:'list', q:'', page:'1', limit:'100'}).toString(), {credentials:'same-origin'})
        .then(r=>r.json()).then(j=>{
          if(!j.ok) throw new Error(j.error||'Load failed');
          const rec = (j.data.items||[]).find(x=> String(x.id)===String(id));
          if (!rec) return;
          form.team.value    = rec.team||'';
          form.date.value    = rec.visit_date||'';
          form.status.value  = rec.status||'planned';
          form.purpose.value = rec.purpose||'';
          form.notes.value   = rec.notes||'';
        }).catch(console.error);
    } else {
      form.status.value = 'planned';
    }
  }

  function closeModal(){
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  cancelBtn?.addEventListener('click', (e)=>{ e.preventDefault(); closeModal(); });
  modal?.addEventListener('click', (e)=>{ if (e.target.dataset.close==='true') closeModal(); });
  addBtn?.addEventListener('click', ()=> openModal(0));

  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const fd = new FormData(form);
    fd.append('action','save');
    const id = form.dataset.id||'';
    if (id) fd.append('id', id);

    fetch(API, {method:'POST', credentials:'same-origin', body: fd})
      .then(r=>r.json())
      .then(j=>{ if (!j.ok) throw new Error(j.error||'Save failed'); closeModal(); list(); })
      .catch(err=> alert(err.message||'Save failed'));
  });

  applyBtn?.addEventListener('click', ()=>{ page=1; list(); });
  [searchIn,statusSel,fromIn,toIn].forEach(el=>{
    el?.addEventListener('keydown', (e)=>{ if (e.key==='Enter'){ page=1; list(); }});
  });
  prevBtn?.addEventListener('click', ()=>{ if(page>1){ page--; list(); } });
  nextBtn?.addEventListener('click', ()=>{ if(page<pages){ page++; list(); } });

  exportBtn?.addEventListener('click', ()=>{
    const params = new URLSearchParams({
      action:'export',
      q:  searchIn?.value?.trim()||'',
      status: statusSel?.value||'all',
      from: fromIn?.value||'',
      to:   toIn?.value||'',
    });
    window.location.href = API + '?' + params.toString();
  });

  list().catch(console.error);
})();