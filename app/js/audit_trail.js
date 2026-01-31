(function() {
    const API = 'audit_trail_api.php';
    const logList = document.getElementById('logList');
    const searchIn = document.getElementById('logSearch');

    const esc = s => (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));

    function getActionConfig(action) {
        const configs = {
            'CREATE': { icon: 'fa-circle-plus', color: 'text-emerald-500' },
            'UPDATE': { icon: 'fa-circle-check', color: 'text-indigo-500' },
            'DELETE': { icon: 'fa-circle-xmark', color: 'text-rose-500' },
            'ARCHIVE': { icon: 'fa-box-archive', color: 'text-amber-500' },
            'RESTORE': { icon: 'fa-clock-rotate-left', color: 'text-cyan-500' },
            'LOGIN': { icon: 'fa-shield-halved', color: 'text-slate-400' }
        };
        return configs[action] || { icon: 'fa-circle-dot', color: 'text-slate-300' };
    }

    function logRow(log) {
        const config = getActionConfig(log.action);
        const time = new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
        const date = new Date(log.created_at).toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });

        return `
        <div class="grid grid-cols-12 gap-6 px-10 py-6 items-center hover:bg-slate-50/50 transition-all duration-200 group">
            <!-- Timestamp Column -->
            <div class="col-span-2 text-center flex flex-col border-r border-slate-100 pr-6">
                <span class="text-sm font-black text-slate-800 tracking-tighter">${time}</span>
                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest mt-0.5">${date}</span>
            </div>

            <!-- Activity Description Column -->
            <div class="col-span-7 flex items-start gap-5 pl-2">
                <div class="mt-1 shrink-0 ${config.color}">
                    <i class="fa-solid ${config.icon} text-lg"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-3 mb-1.5">
                        <span class="text-[10px] font-black uppercase tracking-[0.15em] ${config.color}">${log.action}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${log.module}</span>
                    </div>
                    <p class="text-[15px] font-bold text-slate-700 leading-snug group-hover:text-slate-900 transition-colors">${esc(log.description)}</p>
                </div>
            </div>

            <!-- User Context Column -->
            <div class="col-span-3 text-right flex items-center justify-end gap-4">
                <div class="min-w-0">
                    <div class="text-sm font-black text-slate-800 truncate">${esc(log.user_name || 'System')}</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${log.role || 'CORE SYSTEM'}</div>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-xs font-black text-slate-400 border border-slate-200 shadow-inner">
                    ${(log.user_name || 'S')[0].toUpperCase()}
                </div>
            </div>
        </div>`;
    }

    async function loadLogs(q = '') {
        try {
            const res = await fetch(`${API}?action=list&t=${Date.now()}`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);

            let logs = json.data || [];
            
            if (q) {
                const val = q.toLowerCase();
                logs = logs.filter(l => 
                    l.description.toLowerCase().includes(val) || 
                    (l.user_name || '').toLowerCase().includes(val) ||
                    l.action.toLowerCase().includes(val) ||
                    l.module.toLowerCase().includes(val)
                );
            }

            if (logs.length === 0) {
                logList.innerHTML = `<div class="py-32 text-center space-y-4 bg-white">
                    <div class="text-slate-200"><i class="fa-solid fa-timeline text-7xl"></i></div>
                    <p class="text-slate-400 font-black uppercase tracking-[0.2em] text-xs">No activity logs found.</p>
                </div>`;
                return;
            }

            logList.innerHTML = logs.map(logRow).join('');
        } catch (e) {
            logList.innerHTML = `<div class="p-20 text-rose-500 font-bold text-center">Connection Error: ${e.message}</div>`;
        }
    }

    let t = null;
    searchIn.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => loadLogs(searchIn.value), 250);
    });

    loadLogs();
})();