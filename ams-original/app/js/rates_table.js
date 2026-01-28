
// app/js/rates_table.js — Table-centric UI for Program Rates
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

  function badge(text, cls){
    return `<span class="badge ${cls}">${esc(text||'—')}</span>`;
  }

  function renderRows(list){
    if(!T.tbody) return;
    if(!Array.isArray(list)) list = [];
    T.tbody.innerHTML = list.map(item => {
      const level = item.level || '—';
      const phase = item.phase || '—';
      const status = item.status || 'active';
      return `
        <tr class="hover:bg-slate-50">
          <td class="px-4 py-3">
            <div class="font-medium text-slate-900">${esc(item.code || '')}</div>
            <div class="text-slate-500 text-sm">${esc(item.name || '')}</div>
          </td>
          <td class="px-4 py-3">
            ${badge(level, 'level')}
          </td>
          <td class="px-4 py-3">
            ${badge(phase, 'phase')}
          </td>
          <td class="px-4 py-3">
            ${badge(status, 'status')}
          </td>
          <td class="px-4 py-3 text-right">
            <button class="btn-view inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium bg-blue-600 text-white hover:bg-blue-700"
                    data-program-id="${esc(item.program_id)}">
              View
            </button>
          </td>
        </tr>`;
    }).join('');
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
        T.tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Failed to load programs.</td></tr>`;
      }
    }
  }

  function openModal(){ T.modal?.classList.remove('hidden'); }
  function closeModal(){ T.modal?.classList.add('hidden'); }

  async function showRatings(programId){
    try{
      T.modalTitle.textContent = 'Loading…';
      T.modalScore.textContent = '—';
      T.modalAreas.innerHTML = '';
      T.modalConfirm.setAttribute('disabled','disabled');
      T.modalConfirm.classList.remove('bg-blue-600','text-white');
      T.modalConfirm.classList.add('bg-slate-200','text-slate-500','cursor-not-allowed');

      openModal();
      const res = await fetch(`rates_detail_api.php?program_id=${encodeURIComponent(programId)}&t=${Date.now()}`);
      const j = await res.json();
      if(!j.ok) throw new Error(j.error || 'Failed to load ratings');
      const d = j.data;
      T.modalTitle.textContent = `${d.program_code} ${d.level} ${d.phase}`;
      T.modalScore.textContent = (d.total_score ?? 0).toFixed(1);
      T.modalAreas.innerHTML = (d.areas || []).map(a => `<li>• ${esc(a.name)}</li>`).join('') || '<li class="text-slate-500">No areas yet.</li>';
    }catch(err){
      alert(err.message || 'Unable to open ratings.');
    }
  }

  // Events
  T.modalClose?.addEventListener('click', closeModal);
  T.modal?.addEventListener('click', (e)=>{ if(e.target === T.modal) closeModal(); });
  T.search?.addEventListener('input', (e)=> filter(e.target.value));

  (T.tbody || document).addEventListener('click', (e)=>{
    const btn = e.target.closest('.btn-view');
    if(btn){
      e.preventDefault();
      const pid = btn.getAttribute('data-program-id');
      if(pid) showRatings(pid);
    }
  });

  // Init
  document.addEventListener('DOMContentLoaded', load);
})();
