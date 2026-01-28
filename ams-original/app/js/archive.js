(function() {
    // APIs
    // NOTE: We will route ALL restore actions to archive_api.php because it handles the Audit Trail logic.
    const API_ARCHIVE   = 'archive_api.php';

    // Elements
    const docSection  = document.getElementById('documentSection');
    const progSection = document.getElementById('programSection');
    const docList     = document.getElementById('docList');
    const progList    = document.getElementById('progList');
    const tabDocs     = document.getElementById('tabDocs');
    const tabProgs    = document.getElementById('tabProgs');

    // Utility
    function esc(s) { return (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])); }
    function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'}) : 'N/A'; }

    // --- TAB SWITCHER ---
    window.switchTab = function(type) {
        if (type === 'documents') {
            docSection.classList.remove('hidden');
            progSection.classList.add('hidden');
            
            // Update Tab Styles
            tabDocs.classList.add('border-blue-600', 'text-blue-600');
            tabDocs.classList.remove('border-transparent', 'text-gray-500');
            tabProgs.classList.remove('border-blue-600', 'text-blue-600');
            tabProgs.classList.add('border-transparent', 'text-gray-500');

            loadDocuments();
        } else {
            docSection.classList.add('hidden');
            progSection.classList.remove('hidden');

            // Update Tab Styles
            tabProgs.classList.add('border-blue-600', 'text-blue-600');
            tabProgs.classList.remove('border-transparent', 'text-gray-500');
            tabDocs.classList.remove('border-blue-600', 'text-blue-600');
            tabDocs.classList.add('border-transparent', 'text-gray-500');

            loadPrograms();
        }
    }

    // --- LOAD DOCUMENTS ---
    async function loadDocuments() {
        docList.innerHTML = '<tr><td colspan="5" class="p-3 text-center">Loading...</td></tr>';
        try {
            const res = await fetch(`${API_ARCHIVE}?type=documents`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);

            const items = json.data || [];
            if (items.length === 0) {
                docList.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-gray-400 italic">No archived documents found.</td></tr>`;
                return;
            }

            docList.innerHTML = items.map(item => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-medium text-gray-700">${esc(item.title)}</td>
                    <td class="p-3 text-sm text-gray-500">${esc(item.original_name)}</td>
                    <td class="p-3 text-sm text-gray-500">${esc(item.owner_name)}</td>
                    <td class="p-3 text-sm text-gray-500">${formatDate(item.archived_at)}</td>
                    <td class="p-3">
                        <button onclick="restoreDocument(${item.id})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fa-solid fa-box-open mr-1"></i>Restore
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            docList.innerHTML = `<tr><td colspan="5" class="p-3 text-red-500">Error: ${e.message}</td></tr>`;
        }
    }

    // --- LOAD PROGRAMS ---
    async function loadPrograms() {
        progList.innerHTML = '<tr><td colspan="5" class="p-3 text-center">Loading...</td></tr>';
        try {
            const res = await fetch(`${API_ARCHIVE}?type=programs`);
            const json = await res.json();
            if (!json.ok) throw new Error(json.error);

            const items = json.data || [];
            if (items.length === 0) {
                progList.innerHTML = `<tr><td colspan="5" class="p-4 text-center text-gray-400 italic">No archived programs found.</td></tr>`;
                return;
            }

            progList.innerHTML = items.map(item => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-bold text-gray-700">${esc(item.code)}</td>
                    <td class="p-3 text-gray-600">${esc(item.name)}</td>
                    <td class="p-3 text-sm text-gray-500">${esc(item.description || '-')}</td>
                    <td class="p-3 text-sm text-gray-500">${formatDate(item.created_at)}</td>
                    <td class="p-3">
                        <button onclick="restoreProgram(${item.id}, '${esc(item.code)}')" class="text-green-600 hover:text-green-800 text-sm font-medium">
                            <i class="fa-solid fa-rotate-left mr-1"></i>Restore
                        </button>
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            progList.innerHTML = `<tr><td colspan="5" class="p-3 text-red-500">Error: ${e.message}</td></tr>`;
        }
    }

    // --- ACTIONS ---

    // 1. Restore Document
    window.restoreDocument = async (id) => {
        if (!confirm('Unarchive this document? It will return to the active list.')) return;
        
        try {
            // FIXED: Pointing to archive_api.php with standardized JSON
            const res = await fetch(API_ARCHIVE, { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'restore', 
                    id: id, 
                    type: 'documents' 
                })
            });
            const json = await res.json();
            
            if (json.ok) {
                // Success! Refresh the list
                loadDocuments(); 
            } else {
                alert('Failed: ' + json.error);
            }
        } catch(e) {
            alert('System Error: ' + e.message);
        }
    };

    // 2. Restore Program
    window.restoreProgram = async (id, code) => {
        if (!confirm(`Restore Program "${code}"? It will become active again.`)) return;
        
        try {
            // FIXED: Pointing to archive_api.php with standardized JSON
            const res = await fetch(API_ARCHIVE, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ 
                    action: 'restore', 
                    id: id, 
                    type: 'programs' 
                })
            });
            const json = await res.json();
            
            if (json.ok) {
                // Success! Refresh the list
                loadPrograms();
            } else {
                alert('Failed: ' + json.error);
            }
        } catch(e) {
            alert('System Error: ' + e.message);
        }
    };

    // Init: Load Documents by default
    loadDocuments();

})();