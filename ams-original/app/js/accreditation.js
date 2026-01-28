
// app/js/accreditation.js — Verify Mode on Program Rate cards
(function () {
  const API = 'accreditation_api.php';
  const grid = document.getElementById('accredCardGrid');
  const tbody = document.getElementById('accredTableBody');
  const verifyBtn = document.getElementById('verifyRatesBtn');

  // State
  let verifyMode = false;
  const verified = new Set(); // program_id set

  function safeLabel(x, fallback='—'){ return (x===null||x===undefined||x==='') ? fallback : x; }
  function esc(s) {
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
  }

  function verifyIcon(pid) {
    const isChecked = verified.has(pid);
    // outline vs filled check circle
    const svgOutline = `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
       <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
       <path d="M8.5 12.5l2.2 2.2L15.5 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
     </svg>`;
    const svgFilled = `<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
       <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"></path>
       <path d="M8.5 12.5l2.2 2.2L15.5 10" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
     </svg>`;
    return `<button class="js-verify inline-flex items-center justify-center w-9 h-9 rounded-full ${isChecked ? 'bg-green-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}"
              title="${isChecked ? 'Verified' : 'Verify'}" data-program-id="${pid}" aria-pressed="${isChecked}">
              ${isChecked ? svgFilled : svgOutline}
            </button>`;
  }

  function card(p) {
    // render a single card; verify icon appears only when verifyMode is on
    return `
      <div class="panel p-4 rounded-xl bg-white shadow-sm border border-slate-200" data-program-id="${esc(p.program_id)}

  function row(p){
    return `
      <tr data-program-id="${esc(p.program_id)}">
        <td class="px-4 py-3 font-semibold">${esc(p.code || '')}</td>
        <td class="px-4 py-3">${esc(p.name || '')}</td>
        <td class="px-4 py-3"><span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 border border-blue-200">${esc(p.level || '—')}</span></td>
        <td class="px-4 py-3">
          ${verifyMode ? verifyIcon(p.program_id) : '<span class="text-slate-400 text-xs">—</span>'}
        </td>
        <td class="px-4 py-3 text-right">
          <button type="button" class="btn-view inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700" data-program-id="${p.program_id}">View</button>
        </td>
      </tr>`;
  }
">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="text-base md:text-lg font-semibold">${esc(p.code || p.name || 'Program')}</div>
            <div class="text-sm text-slate-600 mt-0.5">${esc(p.level || 'Level ?')}</div>
          </div>
          ${verifyMode ? verifyIcon(p.program_id) : ''}
        </div>
        <div class="mt-3 flex justify-end">
          <button type="button" class="btn-view px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700" data-program-id="${p.program_id}">View</button>
        </div>
      </div>`;
  }

  async function load() {
    grid.innerHTML = `<div class="p-6 text-slate-500">Loading…</div>`;
    const res = await fetch(`${API}?t=${Date.now()}`);
    const json = await res.json();
    if (!json.ok) {
      grid.innerHTML = `<div class="text-red-600">${esc(json.error || 'Failed to load.')}</div>`;
      return;
    }
    const list = json.data || [];
    if (list.length === 0) {
      grid.innerHTML = `<div class="panel p-6 text-slate-600">No programs yet.</div>`;
      return;
    }
    if(tbody){ tbody.innerHTML = list.map(row).join(''); } else { grid.innerHTML = list.map(card).join(''); }
  }

  // Toggle verify mode
  function setVerifyMode(on) {
    verifyMode = on;
    const label = verifyBtn?.querySelector('.label');
    if (verifyBtn) {
      verifyBtn.classList.remove('bg-green-600','hover:bg-green-700','bg-slate-700','hover:bg-slate-800','bg-blue-600','hover:bg-blue-700');
      verifyBtn.classList.add('bg-blue-700','hover:bg-blue-800');
      if (label) label.textContent = on ? 'Done' : 'Verify Rates';
    }
    // Re-render cards so icons appear/disappear
    load().catch(console.error);
  }

  // Button click
  verifyBtn && verifyBtn.addEventListener('click', () => setVerifyMode(!verifyMode));

  // Event delegation for verify icon toggles
  (tbody || grid) && (tbody || grid).addEventListener('click', (e) => {
    const btn = e.target.closest && e.target.closest('.js-verify');
    if (!btn) return;
    const pid = parseInt(btn.getAttribute('data-program-id'), 10);
    if (!pid) return;

    if (verified.has(pid)) { verified.delete(pid); }
    else { verified.add(pid); }

    // Update just this button UI
    const isChecked = verified.has(pid);
    btn.setAttribute('aria-pressed', String(isChecked));
    btn.classList.toggle('bg-green-600', isChecked);
    btn.classList.toggle('text-white', isChecked);
    btn.classList.toggle('bg-slate-100', !isChecked);
    btn.classList.toggle('text-slate-600', !isChecked);
    btn.innerHTML = isChecked
      ? `<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
           <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"></path>
           <path d="M8.5 12.5l2.2 2.2L15.5 10" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
         </svg>`
      : `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
           <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
           <path d="M8.5 12.5l2.2 2.2L15.5 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
         </svg>`;
  });

  // Initial load
  load().catch(console.error);

  // --- Ratings Modal ---
  const modal = document.getElementById('ratingsModal');
  const modalClose = document.getElementById('ratingsClose');
  const titleEl = document.getElementById('ratingsModalTitle');
  const scoreEl = document.getElementById('ratingsModalScore');
  const areasEl = document.getElementById('ratingsModalAreas');
  const confirmEl = document.getElementById('ratingsConfirm');

  function openModal(){ modal.classList.remove('hidden'); }
  function closeModal(){ modal.classList.add('hidden'); }
  modalClose?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

  async function showRatings(programId){
    try{
      const res = await fetch(`rates_detail_api.php?program_id=${encodeURIComponent(programId)}&t=${Date.now()}`);
      const j = await res.json();
      if(!j.ok) throw new Error(j.error || 'Failed to load ratings');
      const d = j.data;
      titleEl.textContent = `${d.program_code} ${d.level} ${d.phase}`;
      scoreEl.textContent = (d.total_score ?? 0).toFixed(1);
      areasEl.innerHTML = (d.areas || []).map(a => `<li>• ${a.name}</li>`).join('') || '<li class="text-slate-500">No areas yet.</li>';
      confirmEl.setAttribute('disabled', 'disabled');
      confirmEl.classList.remove('bg-blue-600','text-white');
      confirmEl.classList.add('bg-slate-200','text-slate-500','cursor-not-allowed');
      openModal();
    }catch(err){
      alert(err.message || 'Unable to open ratings.');
    }
  }

  // Event delegation for View clicks
  (tbody || grid).addEventListener('click', (e)=>{
    const btn = e.target.closest('.btn-view');
    if(btn){
      e.preventDefault();
      const pid = btn.getAttribute('data-program-id');
      if(pid) showRatings(pid);
    }
  });

})();
