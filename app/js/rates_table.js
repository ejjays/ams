// app/js/rates_table.js — Modern Table-centric UI for Program Rates
(function(){
  const T = {
    tbody: document.getElementById('accredTableBody'),
    search: document.getElementById('ratesSearch'),
    verifyBtn: document.getElementById('verifyRatesBtn'),
    modal: document.getElementById('ratingsModal'),
    modalClose: document.getElementById('ratingsModalClose'),
    modalTitle: document.getElementById('ratingsModalTitle'),
    modalScore: document.getElementById('ratingsModalScore'),
    modalAreas: document.getElementById('ratingsModalAreas'),
    modalConfirm: document.getElementById('ratingsConfirm'),
  };

  const state = {
    rows: [],
    filtered: [],
    verifyMode: false
  };

  function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  function badge(text, type){
    const base = "px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest ring-1";
    const maps = {
      level: 'bg-indigo-50 text-indigo-700 ring-indigo-100',
      phase: 'bg-purple-50 text-purple-700 ring-purple-100',
      status: {
        active: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        inactive: 'bg-rose-50 text-rose-700 ring-rose-100',
        default: 'bg-slate-50 text-slate-700 ring-slate-100'
      }
    };

    let cls = "";
    if(type === 'status'){
      cls = maps.status[String(text).toLowerCase()] || maps.status.default;
    } else {
      cls = maps[type] || maps.status.default;
    }

    return `<span class="${base} ${cls}">${esc(text||'—')}</span>`;
  }

  function renderRows(list){
    if(!T.tbody) return;
    if(!Array.isArray(list)) list = [];
    T.tbody.innerHTML = list.map(item => {
      const level = item.level || '—';
      const phase = item.phase || '—';
      const status = item.status || 'active';
      return `
        <tr class="hover:bg-slate-50/50 transition-all duration-200 group">
          <td class="px-8 py-6">
            <span class="text-[15px] font-black text-slate-900 tracking-tight leading-none block mb-1">${esc(item.code || '')}</span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">${esc(item.name || '')}</span>
          </td>
          <td class="px-8 py-6">
            ${badge(level, 'level')}
          </td>
          <td class="px-8 py-6">
            ${badge(phase, 'phase')}
          </td>
          <td class="px-8 py-6">
            ${badge(status, 'status')}
          </td>
          <td class="px-8 py-6 text-right">
            <button class="btn-view w-11 h-11 inline-flex items-center justify-center rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-300 hover:shadow-xl hover:shadow-blue-100/50 transition-all active:scale-95 group-hover:border-blue-200"
                    data-program-id="${esc(item.program_id)}" title="View Performance">
              <i class="fa-solid fa-chart-simple text-sm pointer-events-none"></i>
            </button>
          </td>
        </tr>`;
    }).join('') || `<tr><td colspan="5" class="px-8 py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">No programs found.</td></tr>`;
  }

  function filter(q){
    q = (q||'').toLowerCase();
    state.filtered = state.rows.filter(r => {
      const hay = `${r.code||''} ${r.name||''} ${r.level||''} ${r.phase||''} ${r.status||''}`.toLowerCase();
      return hay.includes(q);
    });
    renderRows(state.filtered);
  }

  async function load(){
    try{
      const res = await fetch('accreditation_api.php?t=' + Date.now());
      const j = await res.json();
      if(!j.ok) throw new Error(j.error || 'Failed loading programs');
      state.rows = Array.isArray(j.data) ? j.data : [];
      state.filtered = state.rows.slice();
      renderRows(state.filtered);
    }catch(e){
      console.error(e);
      if(T.tbody){
        T.tbody.innerHTML = `<tr><td colspan="5" class="px-8 py-20 text-center text-rose-500 font-black uppercase tracking-widest text-xs">Failed to load programs.</td></tr>`;
      }
    }
  }

  function openModal(){ 
    T.modal?.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  function closeModal(){ 
    T.modal?.classList.add('hidden'); 
    document.body.classList.remove('overflow-hidden');
  }

  async function showRatings(programId){
    try{
      T.modalTitle.textContent = 'Analyzing…';
      T.modalScore.textContent = '—';
      T.modalAreas.innerHTML = '<div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl animate-pulse"><div class="w-2 h-2 rounded-full bg-slate-300"></div><div class="h-2 w-24 bg-slate-200 rounded"></div></div>';
      T.modalConfirm.setAttribute('disabled','disabled');
      T.modalConfirm.className = "px-8 py-3 rounded-2xl bg-slate-100 text-slate-400 text-xs font-black uppercase tracking-widest cursor-not-allowed transition-all";

      openModal();
      const res = await fetch(`rates_detail_api.php?program_id=${encodeURIComponent(programId)}&t=${Date.now()}`);
      const j = await res.json();
      if(!j.ok) throw new Error(j.error || 'Failed to load ratings');
      const d = j.data;
      
      T.modalTitle.textContent = d.program_code;
      T.modalScore.textContent = (d.total_score ?? 0).toFixed(1);
      
      T.modalAreas.innerHTML = (d.areas || []).map(a => `
        <li class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100/50 hover:bg-white hover:shadow-sm transition-all group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-white flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fa-solid fa-layer-group text-[10px]"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">${esc(a.name)}</span>
            </div>
            <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
        </li>
      `).join('') || '<li class="text-slate-400 text-xs font-bold uppercase tracking-widest text-center py-4 italic">No performance data yet.</li>';

      // Logic for enabling confirm if needed... (optional for now)
    }catch(err){
      alert(err.message || 'Unable to open ratings.');
      closeModal();
    }
  }

  // Events
  T.modalClose?.addEventListener('click', closeModal);
  T.modal?.addEventListener('click', (e)=>{ if(e.target.dataset.close === 'true') closeModal(); });
  T.search?.addEventListener('input', (e)=> filter(e.target.value));

  (T.tbody || document).addEventListener('click', (e)=>{
    const btn = e.target.closest('.btn-view');
    if(btn){
      e.preventDefault();
      const pid = btn.getAttribute('data-program-id');
      if(pid) showRatings(pid);
    }
  });

  // Close with button inside modal
  document.getElementById('ratingsModalCloseBtn')?.addEventListener('click', closeModal);

  // Init
  document.addEventListener('DOMContentLoaded', load);
})();