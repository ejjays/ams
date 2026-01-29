(function () {
  const API = 'programs_api.php';

  const grid = document.getElementById('programList');
  const createBt = document.getElementById('programCreateBtn');
  const searchIn = document.getElementById('programSearch');

  const modal = document.getElementById('programCreateModal');
  const mtitle = document.getElementById('programModalTitle');
  const form = document.getElementById('programCreateForm');
  const cancel = document.getElementById('programCreateCancel');

  const $ = id => document.getElementById(id);

  function showToast(msg, type = 'info') {
    const container = $('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    const colors = { success: 'bg-emerald-600', error: 'bg-rose-600', info: 'bg-indigo-600' };
    const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info' };
    toast.className = `flex items-center gap-3 px-6 py-3.5 rounded-2xl text-white text-sm font-bold shadow-xl transition-all duration-500 translate-y-[-20px] opacity-0 ${colors[type] || colors.info}`;
    toast.innerHTML = `<i class="fa-solid ${icons[type] || icons.info}"></i> <span>${msg}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-y-[-20px]', 'opacity-0'), 10);
    setTimeout(() => {
      toast.classList.add('translate-y-[-20px]', 'opacity-0');
      setTimeout(() => toast.remove(), 500);
    }, 4000);
  }

  function openModal(el) {
    if (!el) return;
    el.classList.remove('hidden', 'closing');
    el.style.display = 'flex';
  }
  function closeModal(el) {
    if (!el || el.classList.contains('hidden')) return;
    el.classList.add('closing');
    setTimeout(() => {
      el.classList.add('hidden');
      el.classList.remove('closing');
      el.style.display = 'none';
    }, 150);
  }

  document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-backdrop')) {
      const m = e.target.closest('.modal');
      if (m) closeModal(m);
    }
    const closer = e.target.closest('[data-close="true"]');
    if (closer) {
      const m = closer.closest('.modal');
      if (m) closeModal(m);
    }
  });

  const esc = s => (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

  function cardView(p) {
    const code = esc(p.code);
    const name = esc(p.name);
    const created = (p.created_at || '').slice(0, 10);

    return `
    <div class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-indigo-100 transition-all duration-300 p-6 flex items-center justify-between gap-8">
      <div class="flex items-center gap-8 flex-1 min-w-0">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 text-white flex items-center justify-center font-black text-xs shadow-lg shadow-indigo-200 border border-white/20 shrink-0 group-hover:scale-105 transition-transform duration-500">
          ${code}
        </div>
        
        <div class="flex-1 min-w-0">
          <div class="font-bold text-slate-800 leading-snug text-xl mb-1 group-hover:text-indigo-600 transition-colors truncate">${name}</div>
          <div class="flex items-center gap-4">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-fingerprint text-[8px] text-slate-300"></i> ID: ${p.id}
            </span>
            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                <i class="fa-regular fa-clock text-[8px] text-slate-300"></i> Added ${created}
            </span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 shrink-0">
        <button class="w-11 h-11 rounded-2xl bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-indigo-100 border border-indigo-100 hover:border-indigo-600" title="Edit" data-edit='${JSON.stringify(p)}'>
          <i class="fa-solid fa-pen-to-square text-sm"></i>
        </button>
        <button class="w-11 h-11 rounded-2xl bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-amber-100 border border-amber-100 hover:border-amber-500" title="Archive" data-archive="${p.id}" data-name="${code}">
          <i class="fa-solid fa-box-archive text-sm"></i>
        </button>
      </div>
    </div>`;
  }

  function render(list) {
    if (!list || !list.length) {
      grid.innerHTML = `<div class="py-20 text-center space-y-4 bg-white rounded-3xl border-2 border-dashed border-slate-200">
        <div class="text-slate-200"><i class="fa-solid fa-folder-open text-6xl"></i></div>
        <div class="text-slate-400 font-black uppercase tracking-[0.2em] text-[10px]">No active programs found.</div>
      </div>`;
      return;
    }
    grid.innerHTML = list.map(cardView).join('');

    grid.querySelectorAll('[data-edit]').forEach(btn => {
      btn.addEventListener('click', () => {
        const p = JSON.parse(btn.getAttribute('data-edit'));
        mtitle.textContent = 'Edit Program';
        if ($('programSubmitBtn')) $('programSubmitBtn').textContent = 'Update Program';
        $('program_id').value = p.id;
        $('program_code').value = p.code || '';
        $('program_name').value = p.name || '';
        openModal(modal);
      });
    });

    grid.querySelectorAll('[data-archive]').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-archive');
        const name = btn.getAttribute('data-name');
        
        $('archiveProgramName').textContent = name;
        openModal($('archiveModal'));

        const confirmBtn = $('archiveConfirmBtn');
        // Remove old listener to avoid multiple triggers
        const newConfirm = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);

        newConfirm.addEventListener('click', async () => {
          newConfirm.disabled = true;
          newConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Archiving...';
          
          try {
            const res = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
            const json = await res.json();
            if (json.ok) {
              showToast(`Program ${name} archived successfully.`, 'success');
              closeModal($('archiveModal'));
              load(getQ());
            } else {
              showToast(json.error || 'Archive failed', 'error');
            }
          } catch (e) {
            showToast("Connection failed.", "error");
          } finally {
            newConfirm.disabled = false;
            newConfirm.textContent = 'Yes, Archive';
          }
        });
      });
    });
  }

  async function load(q = '') {
    const res = await fetch(`${API}?q=${encodeURIComponent(q)}&t=${Date.now()}`);
    const json = await res.json();
    render(json.ok ? (json.data || []) : []);
  }

  function getQ() { return (searchIn?.value || '').trim(); }

  createBt?.addEventListener('click', () => {
    mtitle.textContent = 'New Program';
    if ($('programSubmitBtn')) $('programSubmitBtn').textContent = 'Save Program';
    form.reset();
    $('program_id').value = '';
    openModal(modal);
  });

  cancel?.addEventListener('click', () => closeModal(modal));

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    
    let res;
    if (id) {
      const payload = { id, code: fd.get('code'), name: fd.get('name') };
      res = await fetch(API, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    } else {
      res = await fetch(API, { method: 'POST', body: fd });
    }

    const json = await res.json();
    if (json.ok) {
      showToast(id ? "Program updated." : "Program created.", "success");
      closeModal(modal);
      load(getQ());
    } else {
      showToast(json.error || "Operation failed", "error");
    }
  });

  let t = null;
  searchIn?.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => load(getQ()), 250);
  });

  load().catch(console.error);
})();