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

  // Modern status badge
  function badge(st){
    const key = String(st||'').toLowerCase();
    const labelMap = { planned:'Planned', ongoing:'Ongoing', completed:'Completed', cancelled:'Cancelled' };
    const clsMap = {
      planned:   'bg-blue-50      text-blue-700     ring-blue-100',
      ongoing:   'bg-amber-50    text-amber-700   ring-amber-100',
      completed: 'bg-emerald-50  text-emerald-700 ring-emerald-200',
      cancelled: 'bg-rose-50     text-rose-700    ring-rose-100',
    };
    const label = labelMap[key] || (st || '');
    const cls   = clsMap[key] || 'bg-gray-100 text-gray-700 ring-gray-100';
    return `<span class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest ring-1 ${cls}">${esc(label)}</span>`;
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
      <tr class="hover:bg-slate-50/50 transition-all duration-200 group border-b border-slate-50 last:border-0">
        <td class="px-8 py-6">
            <span class="text-[15px] font-black text-slate-900 tracking-tight leading-none block">${esc(r.team||'')}</span>
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-1 block">Official Visitor</span>
        </td>
        <td class="px-8 py-6">
            <div class="flex items-center gap-2 text-slate-400">
                <i class="fa-regular fa-calendar text-[10px]"></i>
                <span class="text-xs font-black uppercase tracking-[0.15em] whitespace-nowrap">${esc(r.visit_date||'')}</span>
            </div>
        </td>
        <td class="px-8 py-6 text-sm font-extrabold text-slate-700 uppercase tracking-tight">${esc(r.purpose||'')}</td>
        <td class="px-8 py-6">${badge(r.status)}</td>
        <td class="px-8 py-6">
            <p class="text-xs font-semibold text-slate-500 italic leading-relaxed max-w-[240px] truncate" title="${esc(r.notes||'')}">
                "${esc(r.notes||'') || 'No additional notes provided.'}"
            </p>
        </td>
        <td class="px-8 py-6 text-right">
          <button class="w-11 h-11 inline-flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-100/50 transition-all active:scale-95 group-hover:border-blue-200" data-edit="${r.id}" title="Edit Record">
            <i class="fa-solid fa-pen-nib text-sm pointer-events-none"></i>
          </button>
        </td>
      </tr>
    `).join('') || `<tr><td colspan="6" class="px-8 py-24 text-center">
        <div class="flex flex-col items-center justify-center space-y-4">
            <div class="w-16 h-16 rounded-3xl bg-slate-50 flex items-center justify-center text-slate-200">
                <i class="fa-solid fa-calendar-xmark text-3xl"></i>
            </div>
            <div>
                <p class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">No visits found</p>
                <p class="text-xs font-bold text-slate-300 mt-1 uppercase tracking-widest">Try adjusting your filters</p>
            </div>
        </div>
    </td></tr>`;

    resultTxt.textContent = total + ' ' + (total===1?'RECORD FOUND':'RECORDS FOUND');
    pageLbl.textContent = String(page);
    prevBtn.disabled = page<=1;
    nextBtn.disabled = page>=pages;
  }

  tableBody?.addEventListener('click', (e)=>{
    const t = e.target;
    if (t.closest('[data-edit]')){
      const id = t.closest('[data-edit]').dataset.edit;
      openModal(parseInt(id,10));
    }
  });

  function openModal(id){
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    form.reset();
    form.dataset.id = id?String(id):'';
    modalTitle.textContent = id ? 'Edit Visit' : 'Add Visit';

    if (id){
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
  searchIn?.addEventListener('input', () => { page = 1; list(); });
  [searchIn,statusSel,fromIn,toIn].forEach(el=>{
    el?.addEventListener('keydown', (e)=>{ if (e.key==='Enter'){ page=1; list(); }});
  });

  // Re-trigger search on change for dropdowns and dates
  [statusSel, fromIn, toIn].forEach(el => {
    el?.addEventListener('change', () => { page = 1; list(); });
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