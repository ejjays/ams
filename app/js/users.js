// app/js/users.js — table UI (v3 - Delete Removed)
(function () {
  const API = 'users_api.php';

  // DOM
  const $ = (id) => document.getElementById(id);
  const usersTBody      = $('usersTableBody');
  const userSearch      = $('userSearch');
  const userCreateBtn   = $('userCreateBtn');

  // Create/Edit modal
  const userModal       = $('userModal');
  const userModalTitle  = $('userModalTitle');
  const userForm        = $('userForm');
  const userCancelBtn   = $('userCancelBtn');

  // Password group + input
  const passwordGroup   = $('passwordGroup');
  const passwordInput   = $('password');

  // Utils
  function escapeHtml(s){ return (s||'').replace(/[&<>\"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
  function open(el){ el?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function close(el){ el?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

  // Row template
  function row(u){
    const fullName = [u.first_name, u.last_name].filter(Boolean).join(' ') || u.username || '(no name)';
    const nameHtml = `<div class="font-medium text-slate-800">${escapeHtml(fullName)}</div>`;
    const displayRole = u.role || 'faculty';
    return `
      <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 hover:shadow-sm transition my-2">
        <div class="grid grid-cols-12 gap-4 items-center">
          <div class="col-span-3">
            ${nameHtml}
            ${u.id ? `<div class="text-xs text-slate-500">#${u.id}</div>` : ''}
          </div>
          <div class="col-span-3">${escapeHtml(u.email || '')}</div>
          <div class="col-span-2">${escapeHtml(u.username || '')}</div>
          <div class="col-span-2 capitalize">${escapeHtml(displayRole)}</div>
          <div class="col-span-2">
            <div class="flex items-center gap-4 justify-end">
              <button class="text-blue-700 hover:underline" data-edit='${JSON.stringify(u)}'>Edit</button>
            </div>
          </div>
        </div>
      </div>`;
  }

  // Render list
  function render(list){
    if (!(list && list.length)) {
      usersTBody.innerHTML = '<div class="px-5 py-6 text-center text-slate-500">No users found.</div>';
      return;
    }
    usersTBody.innerHTML = list.map(row).join('');

    // Edit buttons
    usersTBody.querySelectorAll('[data-edit]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        let u; try { u = JSON.parse(btn.getAttribute('data-edit')); } catch { u = {}; }
        userModalTitle.textContent = 'Edit User';

        // Populate fields
        $('id').value         = u.id || '';
        $('first_name').value = u.first_name || '';
        $('last_name').value  = u.last_name  || '';
        $('email').value      = u.email      || '';
        $('username').value   = u.username   || '';
        $('role').value       = u.role || 'faculty';

        // Hide password section on EDIT and clear/disable any value
        if (passwordGroup) passwordGroup.classList.add('hidden');
        if (passwordInput) {
          passwordInput.value = '';
          passwordInput.removeAttribute('required');
        }

        open(userModal);
      });
    });
  }

  // Fetch list
  async function listUsers(q=''){
    const res = await fetch(`${API}?q=${encodeURIComponent(q)}`);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Failed to load users');
    render(json.data || []);
  }

  // Create new
  userCreateBtn?.addEventListener('click', ()=>{
    userModalTitle.textContent = 'Create User';
    userForm.reset();
    $('id').value = '';
    $('role').value = 'faculty';

    // Show password group on CREATE and enforce required
    if (passwordGroup) passwordGroup.classList.remove('hidden');
    if (passwordInput) passwordInput.setAttribute('required','required');

    open(userModal);
  });

  // Cancel modal
  userCancelBtn?.addEventListener('click', ()=> close(userModal));

  // Save (create/update)
  userForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(userForm);
    const id = (fd.get('id')||'').trim();
    let ok = false;

    if (id) {
      // UPDATE: do NOT send password
      const payload = {
        id:         id,
        first_name: fd.get('first_name') || '',
        last_name:  fd.get('last_name') || '',
        email:      fd.get('email') || '',
        username:   fd.get('username') || '',
        role:       fd.get('role') || 'faculty'
      };
      const res = await fetch(API, {
        method: 'PUT',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      ok = json.ok;
      if (!ok) alert(json.error || 'Update failed');
    } else {
      // CREATE: require password
      const pwd = (fd.get('password')||'').trim();
      if(!pwd){ alert('Password is required for new users.'); return; }
      // ensure role sent
      if (!fd.has('role') || !fd.get('role')) fd.set('role', 'faculty');

      const res = await fetch(API, { method: 'POST', body: fd });
      const json = await res.json();
      ok = json.ok;
      if (!ok) alert(json.error || 'Create failed');
    }

    if (ok) {
      close(userModal);
      listUsers(userSearch.value.trim()).catch(console.error);
    }
  });

  // Search (debounced)
  let t = null;
  userSearch?.addEventListener('input', ()=>{
    clearTimeout(t);
    t = setTimeout(()=> listUsers(userSearch.value.trim()).catch(console.error), 250);
  });

  // Init
  listUsers().catch(err=>{
    console.error(err);
    usersTBody.innerHTML = '<div class="px-5 py-6 text-center text-red-600">Failed to load users</div>';
  });
})();