(function() {
    const API = 'users_api.php';

    const userList = document.getElementById('userList');
    const createBtn = document.getElementById('userCreateBtn');
    const searchIn = document.getElementById('userSearch');

    const userModal = document.getElementById('userModal');
    const mTitle = document.getElementById('userModalTitle');
    const userForm = document.getElementById('userForm');
    const submitBtn = document.getElementById('userSubmitBtn');

    const deleteModal = document.getElementById('deleteModal');
    const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');

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

    function getRoleBadge(role) {
        const badges = {
            'super_admin': 'bg-rose-50 text-rose-600 border-rose-100',
            'admin': 'bg-cyan-50 text-cyan-600 border-cyan-100',
            'dean': 'bg-indigo-50 text-indigo-600 border-indigo-100',
            'program_coordinator': 'bg-violet-50 text-violet-600 border-violet-100',
            'faculty': 'bg-slate-50 text-slate-600 border-slate-100',
            'staff': 'bg-amber-50 text-amber-600 border-amber-100',
            'external_accreditor': 'bg-emerald-50 text-emerald-600 border-emerald-100'
        };
        const labels = {
            'super_admin': 'Super Admin',
            'admin': 'Administrator',
            'dean': 'Dean',
            'program_coordinator': 'Coordinator',
            'faculty': 'Faculty',
            'staff': 'Staff',
            'external_accreditor': 'Accreditor'
        };
        return `<span class="px-3 py-1 rounded-lg border font-black text-[9px] uppercase tracking-tighter ${badges[role] || badges['faculty']}">
            ${labels[role] || role}
        </span>`;
    }

    function userCard(u) {
        const name = `${esc(u.first_name)} ${esc(u.last_name)}`;
        const initials = `${u.first_name[0]}${u.last_name[0]}`.toUpperCase();
        
        return `
        <div class="group bg-white rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-cyan-100 transition-all duration-300 p-6 flex items-center justify-between gap-8">
            <div class="flex items-center gap-8 flex-1 min-w-0">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 via-blue-600 to-indigo-700 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-cyan-200 border border-white/20 shrink-0 group-hover:scale-105 transition-transform duration-500">
                    ${initials}
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-slate-800 leading-snug text-xl mb-1 group-hover:text-cyan-600 transition-colors truncate">${name}</div>
                    <div class="flex items-center gap-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-envelope text-[8px] text-slate-300"></i> ${esc(u.email)}
                        </span>
                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-user-tag text-[8px] text-slate-300"></i> @${esc(u.username)}
                        </span>
                    </div>
                </div>
            </div>

            <div class="w-48 shrink-0 flex flex-col gap-1">
                <div class="text-[9px] font-black uppercase tracking-widest text-slate-400">Access Level</div>
                <div>${getRoleBadge(u.role)}</div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <button data-edit='${JSON.stringify(u).replace(/'/g, "&apos;")}'
                    class="w-11 h-11 rounded-2xl bg-cyan-50 hover:bg-cyan-600 text-cyan-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-cyan-100 border border-cyan-100 hover:border-cyan-600" title="Edit User">
                    <i class="fa-solid fa-user-pen text-sm"></i>
                </button>
                <button data-delete-id="${u.id}" data-delete-name="${name}"
                    class="w-11 h-11 rounded-2xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white transition-all active:scale-90 flex items-center justify-center shadow-sm shadow-rose-100 border border-rose-100 hover:border-rose-600" title="Delete User">
                    <i class="fa-solid fa-user-minus text-sm"></i>
                </button>
            </div>
        </div>`;
    }

    async function loadUsers(q = '') {
        const res = await fetch(`${API}?q=${encodeURIComponent(q)}&t=${Date.now()}`);
        const json = await res.json();
        
        if (!json.ok) {
            userList.innerHTML = `<div class="p-10 text-rose-500 font-bold text-center">${json.error}</div>`;
            return;
        }

        const list = json.data || [];
        if (list.length === 0) {
            userList.innerHTML = `<div class="py-20 text-center space-y-4 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="text-slate-200"><i class="fa-solid fa-users-slash text-6xl"></i></div>
                <div class="text-slate-400 font-black uppercase tracking-[0.2em] text-[10px]">No users found matching your search.</div>
            </div>`;
            return;
        }

        userList.innerHTML = list.map(userCard).join('');

        // Wire Listeners
        userList.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', () => {
                const u = JSON.parse(btn.getAttribute('data-edit'));
                editUser(u);
            });
        });

        userList.querySelectorAll('[data-delete-id]').forEach(btn => {
            btn.addEventListener('click', () => {
                confirmDelete(btn.getAttribute('data-delete-id'), btn.getAttribute('data-delete-name'));
            });
        });
    }

    createBtn.addEventListener('click', () => {
        mTitle.textContent = 'Add New User';
        submitBtn.textContent = 'Save User';
        userForm.reset();
        $('u_id').value = '';
        $('passHint').classList.add('hidden');
        $('password').required = true;
        openModal(userModal);
    });

    function editUser(u) {
        mTitle.textContent = 'Edit User Details';
        submitBtn.textContent = 'Update User';
        userForm.reset();
        $('u_id').value = u.id;
        $('first_name').value = u.first_name;
        $('last_name').value = u.last_name;
        $('email').value = u.email;
        $('username').value = u.username;
        $('role').value = u.role;
        $('password').required = false;
        $('passHint').classList.remove('hidden');
        openModal(userModal);
    };

    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(userForm);
        const id = fd.get('id');
        
        let res;
        if (id) {
            const data = Object.fromEntries(fd.entries());
            res = await fetch(API, { 
                method: 'PUT', 
                headers: { 'Content-Type': 'application/json' }, 
                body: JSON.stringify(data) 
            });
        } else {
            res = await fetch(API, { method: 'POST', body: fd });
        }

        const json = await res.json();
        if (json.ok) {
            showToast(id ? 'User updated successfully.' : 'User created successfully.', 'success');
            closeModal(userModal);
            loadUsers(searchIn.value);
        } else {
            showToast(json.error || 'Operation failed.', 'error');
        }
    });

    function confirmDelete(id, name) {
        $('deleteUserName').textContent = name;
        openModal(deleteModal);

        const confirmBtn = $('deleteConfirmBtn');
        const newConfirm = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);

        newConfirm.onclick = async () => {
            newConfirm.disabled = true;
            newConfirm.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Deleting...';
            
            try {
                const res = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
                const json = await res.json();
                if (json.ok) {
                    showToast(`${name} has been removed.`, 'success');
                    closeModal(deleteModal);
                    loadUsers(searchIn.value);
                } else {
                    showToast(json.error || 'Delete failed', 'error');
                }
            } catch(e) {
                showToast("Connection error.", "error");
            } finally {
                newConfirm.disabled = false;
                newConfirm.textContent = 'Yes, Delete';
            }
        };
    };

    let t = null;
    searchIn.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => loadUsers(searchIn.value), 250);
    });

    // Init
    loadUsers();
})();