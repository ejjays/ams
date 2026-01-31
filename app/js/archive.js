(function() {
    const API_ARCHIVE = 'archive_api.php';

    const docSection = document.getElementById('documentSection');
    const progSection = document.getElementById('programSection');
    const docList = document.getElementById('docList');
    const progList = document.getElementById('progList');
    const tabDocs = document.getElementById('tabDocs');
    const tabProgs = document.getElementById('tabProgs');

    const $ = id => document.getElementById(id);
    const esc = s => (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    
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

    // --- TAB SWITCHER ---
    window.switchTab = function(type) {
        const activeClass = ['bg-white', 'text-indigo-600', 'shadow-sm', 'shadow-indigo-100', 'border-indigo-100'];
        const inactiveClass = ['text-slate-500', 'hover:text-slate-700'];

        if (type === 'documents') {
            docSection.classList.remove('hidden');
            progSection.classList.add('hidden');
            
            tabDocs.classList.add(...activeClass);
            tabDocs.classList.remove(...inactiveClass);
            tabProgs.classList.remove(...activeClass);
            tabProgs.classList.add(...inactiveClass);

            loadDocuments();
        } else {
            docSection.classList.add('hidden');
            progSection.classList.remove('hidden');

            tabProgs.classList.add(...activeClass);
            tabProgs.classList.remove(...inactiveClass);
            tabDocs.classList.remove(...activeClass);
            tabDocs.classList.add(...inactiveClass);

            loadPrograms();
        }
    }

    // --- LOAD DOCUMENTS ---
    async function loadDocuments() {
        docList.innerHTML = `<div class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">Loading Documents...</div>`;
        try {
            const res = await fetch(`${API_ARCHIVE}?type=documents&t=${Date.now()}`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);

            const items = json.data || [];
            if (items.length === 0) {
                docList.innerHTML = `<div class="py-20 text-center space-y-4 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="text-slate-200"><i class="fa-solid fa-folder-open text-6xl"></i></div>
                    <div class="text-slate-400 font-black uppercase tracking-[0.2em] text-[10px]">No archived documents found.</div>
                </div>`;
                return;
            }

            docList.innerHTML = items.map(item => `
                <div class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-indigo-100 transition-all duration-300 p-6 flex items-center justify-between gap-8">
                    <div class="flex items-center gap-6 flex-1 min-w-0">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 text-indigo-600 flex items-center justify-center font-black text-xs border border-slate-100 shrink-0">
                            <i class="fa-solid fa-file-pdf text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 leading-snug text-lg mb-1 group-hover:text-indigo-600 transition-colors truncate">${esc(item.title)}</div>
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user text-[8px] text-slate-300"></i> ${esc(item.owner_name)}
                                </span>
                                <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-paperclip text-[8px] text-slate-300"></i> ${esc(item.original_name)}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="w-48 text-left shrink-0">
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Archived On</div>
                        <div class="text-sm font-bold text-slate-600">${(item.archived_at || '').slice(0, 10)}</div>
                    </div>
                    <div class="shrink-0">
                        <button onclick="confirmRestore(${item.id}, '${esc(item.title)}', 'documents')" 
                            class="w-11 h-11 rounded-2xl bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-indigo-100 border border-indigo-100 hover:border-indigo-600">
                            <i class="fa-solid fa-rotate-left text-sm"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            docList.innerHTML = `<div class="p-10 text-rose-500 font-bold text-center">${e.message}</div>`;
        }
    }

    // --- LOAD PROGRAMS ---
    async function loadPrograms() {
        progList.innerHTML = `<div class="py-20 text-center text-slate-400 font-bold uppercase tracking-widest text-xs">Loading Programs...</div>`;
        try {
            const res = await fetch(`${API_ARCHIVE}?type=programs&t=${Date.now()}`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);

            const items = json.data || [];
            if (items.length === 0) {
                progList.innerHTML = `<div class="py-20 text-center space-y-4 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <div class="text-slate-200"><i class="fa-solid fa-folder-open text-6xl"></i></div>
                    <div class="text-slate-400 font-black uppercase tracking-[0.2em] text-[10px]">No archived programs found.</div>
                </div>`;
                return;
            }

            progList.innerHTML = items.map(item => `
                <div class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-indigo-100 transition-all duration-300 p-6 flex items-center justify-between gap-8">
                    <div class="flex items-center gap-8 flex-1 min-w-0">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 text-white flex items-center justify-center font-black text-xs shadow-lg shadow-indigo-200 border border-white/20 shrink-0">
                            ${esc(item.code)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-slate-800 leading-snug text-xl mb-1 group-hover:text-indigo-600 transition-colors truncate">${esc(item.name)}</div>
                            <div class="flex items-center gap-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-fingerprint text-[8px] text-slate-300"></i> ID: ${item.id}
                                </span>
                                <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-[8px] text-slate-300"></i> Created ${(item.created_at || '').slice(0, 10)}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="shrink-0">
                        <button onclick="confirmRestore(${item.id}, '${esc(item.code)}', 'programs')" 
                            class="w-11 h-11 rounded-2xl bg-indigo-50 hover:bg-indigo-600 text-indigo-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-indigo-100 border border-indigo-100 hover:border-indigo-600">
                            <i class="fa-solid fa-rotate-left text-sm"></i>
                        </button>
                    </div>
                </div>
            `).join('');
        } catch (e) {
            progList.innerHTML = `<div class="p-10 text-rose-500 font-bold text-center">${e.message}</div>`;
        }
    }

    // --- RESTORE LOGIC ---

    window.confirmRestore = (id, name, type) => {
        $('restoreItemName').textContent = name;
        openModal($('restoreModal'));

        const confirmBtn = $('restoreConfirmBtn');
        const newConfirm = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);

        newConfirm.onclick = async () => {
            newConfirm.disabled = true;
            newConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Restoring...';
            
            try {
                const res = await fetch(API_ARCHIVE, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'restore', id, type })
                });
                const json = await res.json();
                
                if (json.ok) {
                    showToast(`${name} restored successfully.`, 'success');
                    closeModal($('restoreModal'));
                    if (type === 'documents') loadDocuments(); else loadPrograms();
                } else {
                    showToast(json.error || 'Restore failed', 'error');
                }
            } catch(e) {
                showToast("System error occurred.", "error");
            } finally {
                newConfirm.disabled = false;
                newConfirm.textContent = 'Yes, Restore';
            }
        };
    };

    // Init
    loadDocuments();
})();