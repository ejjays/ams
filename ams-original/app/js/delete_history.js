(function() {
    const API = 'delete_history_api.php';
    const listEl = document.getElementById('historyList');

    function esc(s) {
        return (s || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[m]));
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const d = new Date(dateString);
        return d.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function row(item) {
        const title = esc(item.title || item.original_name);
        const file = esc(item.original_name);
        const user = esc(item.deleted_by_name || 'Unknown User');
        const date = formatDate(item.deleted_at);

        return `
            <tr class="border-b hover:bg-gray-50" data-history-id="${item.history_id}">
                <td class="p-3">${title}</td>
                <td class="p-3 text-sm text-gray-600">${file}</td>
                <td class="p-3 text-sm text-gray-600">${user}</td>
                <td class="p-3 text-sm text-gray-600">${date}</td>
                <td class="p-3 text-sm text-gray-600">
                    <button 
                        data-action="restore" 
                        title="Restore"
                        class="px-2 py-1 text-green-600 hover:bg-green-100 rounded">
                        <i class="fa-solid fa-undo"></i> Restore
                    </button>
                    <button 
                        data-action="delete" 
                        title="Delete Permanently"
                        class="px-2 py-1 text-red-600 hover:bg-red-100 rounded ml-2">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    }

    async function loadHistory() {
        if (!listEl) return;
        try {
            const res = await fetch(`${API}?action=list`);
            const json = await res.json();

            if (!json.ok) {
                listEl.innerHTML = `<tr><td colspan="5" class="p-3 text-red-500">${esc(json.error)}</td></tr>`;
                return;
            }

            const items = json.data || [];
            if (items.length === 0) {
                listEl.innerHTML = `<tr><td colspan="5" class="p-3 text-center text-gray-500">No deletion history.</td></tr>`;
            } else {
                listEl.innerHTML = items.map(row).join('');
                addListeners();
            }

        } catch (e) {
            listEl.innerHTML = `<tr><td colspan="5" class="p-3 text-red-500">Failed to load history.</td></tr>`;
            console.error(e);
        }
    }

    function addListeners() {
        listEl.addEventListener('click', async (e) => {
            const btn = e.target.closest('button[data-action]');
            if (!btn) return;

            const tr = btn.closest('tr[data-history-id]');
            const id = tr.dataset.historyId;
            const action = btn.dataset.action;

            if (action === 'restore') {
                if (!confirm('Are you sure you want to restore this file?\nIt will be returned to the main Documents page.')) return;
                
                const res = await fetch(`${API}?action=restore&id=${id}`);
                const json = await res.json();
                if (!json.ok) return alert('Restore failed: ' + json.error);
                loadHistory(); // Reload list
            }

            if (action === 'delete') {
                if (!confirm('DELETE PERMANENTLY?\nThis action cannot be undone. The file will be erased forever.')) return;
                
                const res = await fetch(`${API}?action=perm_delete&id=${id}`);
                const json = await res.json();
                if (!json.ok) return alert('Delete failed: ' + json.error);
                loadHistory(); // Reload list
            }
        });
    }

    loadHistory();
})();