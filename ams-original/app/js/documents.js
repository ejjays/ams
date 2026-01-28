// app/js/documents.js (UPDATED: Removed Archive and Delete buttons)
(function () {
    const API = 'documents_api.php';
    const PUBLIC_VIEWER = 'public_view.php'; // The public file

    const tabMine = document.getElementById('tabMine');
    const tabShared = document.getElementById('tabShared');
    const docList = document.getElementById('docList');
    const search = document.getElementById('docSearch');

    const upOpen = document.getElementById('docUploadOpen');
    const modal = document.getElementById('docModal');
    const mTitle = document.getElementById('docModalTitle');
    const form = document.getElementById('docForm');
    const cancel = document.getElementById('docCancel');
    const fileRow = document.getElementById('fileRow');

    const shareModal = document.getElementById('shareModal');
    const shareForm = document.getElementById('shareForm');
    const shareCancel = document.getElementById('shareCancel');

    // Review modal refs
    const reviewModal = document.getElementById('reviewModal');
    const reviewForm = document.getElementById('reviewForm');
    const reviewCancel = document.getElementById('reviewCancel');
    const reviewTogglePreview = document.getElementById('reviewTogglePreview');
    const reviewPreviewWrap = document.getElementById('reviewPreviewWrap');
    const reviewPreviewFrame = document.getElementById('reviewPreviewFrame');

    let currentTab = 'mine';
    let debounce;

    const $ = id => document.getElementById(id);

    function open(el) { el.classList.remove('hidden'); }
    function close(el) { el.classList.add('hidden'); }

    function extBadge(ext) { return `<span class="text-sm px-2.5 py-1.5 rounded bg-slate-100 text-slate-700 border">${ext || ''}</span>`; }

    function esc(s) { return (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])); }

    function stars(n) {
        if (!n || isNaN(n)) return '';
        const full = '★'.repeat(Math.round(n));
        const empty = '☆'.repeat(5 - Math.round(n));
        return `<span class="text-amber-500">${full}</span><span class="text-slate-300">${empty}</span><span class="ml-1 text-xs text-slate-500">(${Number(n).toFixed(1)})</span>`;
    }

    function card(d) {
        const title = esc(d.title || '(untitled)');
        const cmt = esc(d.comment || '');
        const ext = esc((d.file_ext || '').toUpperCase());
        const owner = d.owner ? `<div class="text-xs text-slate-500 mt-1">by ${esc(d.owner)}</div>` : '';

        // Get rating data
        const ratingLine = (d.avg_rating || d.my_review_rating)
            ? `<div class="text-sm mt-1">${stars(d.my_review_rating || d.avg_rating)}</div>`
            : '';

        // UPDATED: Removed Archive and Delete buttons from controls
        const controls = `
          <button class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-700" title="Edit" data-edit='${JSON.stringify(d)}'><i class="fa-solid fa-pen"></i></button>
          <button class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-700" title="Share" data-share="${d.id}"><i class="fa-solid fa-share-nodes"></i></button>
          <button class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-700" title="Review" data-review="${d.id}" data-stored-name="${esc(d.stored_name)}" data-file-ext="${esc(d.file_ext)}"><i class="fa-solid fa-star"></i></button>`;

        return `
    <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col gap-4 min-h-[140px]">
      <div class="flex items-start justify-between">
        <div>
          <div class="font-semibold text-lg text-slate-800">${title}</div>
          ${cmt ? `<div class="text-sm text-slate-500">${cmt}</div>` : ''}
          ${owner}
          ${ratingLine}
        </div>
        ${extBadge(ext)}
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <a class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-slate-700" title="Download" href="${API}?action=download&id=${d.id}">
          <i class="fa-solid fa-download"></i>
        </a>
        ${controls}
      </div>
    </div>`;
    }

    async function load() {
        const q = search.value.trim();
        console.log(`Loading... Tab: ${currentTab}, Query: "${q}"`);
        const res = await fetch(`${API}?action=list&tab=${currentTab}&q=${encodeURIComponent(q)}`);
        
        if (!res.ok) {
            docList.innerHTML = `<div class="text-slate-500">Error: ${res.statusText}</div>`;
            return;
        }

        const json = await res.json();
        
        if (!json.ok) { 
            docList.innerHTML = `<div class="text-slate-500">${esc(json.error || 'Failed to load data.')}</div>`; 
            return; 
        }
        
        const list = json.data || [];
        docList.innerHTML = list.length ? list.map(card).join('') : `<div class="text-slate-500">No documents.</div>`;
        bindCardEvents();
    }

    function setTab(tab) {
        currentTab = tab;
        if (tab === 'mine') {
            tabMine.className = "py-2 -mb-px border-b-2 border-blue-700 text-blue-700 font-medium";
            tabShared.className = "py-2 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-700";
        } else {
            tabShared.className = "py-2 -mb-px border-b-2 border-blue-700 text-blue-700 font-medium";
            tabMine.className = "py-2 -mb-px border-b-2 border-transparent text-slate-500 hover:text-slate-700";
        }
        load().catch(console.error);
    }

    function bindCardEvents() {
        // edit
        docList.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = JSON.parse(btn.getAttribute('data-edit'));
                mTitle.textContent = 'Edit document';
                $('doc_id').value = d.id;
                $('title').value = d.title || '';
                $('comment').value = d.comment || '';
                $('file').value = '';
                fileRow.classList.remove('hidden');
                open(modal);
            });
        });

        // UPDATED: Removed delete and archive event listeners since buttons are gone

        // share
        docList.querySelectorAll('[data-share]').forEach(btn => {
            btn.addEventListener('click', () => {
                $('share_doc_id').value = btn.getAttribute('data-share');
                $('share_user_id').value = '';
                open(shareModal);
            });
        });

        // review (open modal with inline viewer wired)
        docList.querySelectorAll('[data-review]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-review');
                const storedName = btn.getAttribute('data-stored-name');
                const fileExt = btn.getAttribute('data-file-ext');

                $('review_doc_id').value = id;
                $('review_stored_name').value = storedName;
                $('review_file_ext').value = fileExt;
                $('review_rating').value = '';
                $('review_comment').value = '';

                reviewPreviewFrame.src = 'about:blank';
                reviewPreviewWrap.classList.add('hidden');

                open(reviewModal);
            });
        });

        // Toggle inline preview inside the Review modal
        if (reviewTogglePreview) {
            reviewTogglePreview.addEventListener('click', () => {
                const storedName = $('review_stored_name').value;
                const fileExt = ($('review_file_ext').value || '').toLowerCase();
                if (!storedName) return;

                let viewerUrl = '';
                const officeExts = ['docx', 'doc', 'xlsx', 'xls', 'pptx', 'ppt'];
                const nativeExts = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

                const relativeFileUrl = `${PUBLIC_VIEWER}?file=${encodeURIComponent(storedName)}`;
                const fileUrl = new URL(relativeFileUrl, window.location.href).href;

                if (nativeExts.includes(fileExt)) {
                    viewerUrl = fileUrl;
                } else if (officeExts.includes(fileExt)) {
                    viewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;
                } else {
                    viewerUrl = '';
                }

                if (reviewPreviewWrap.classList.contains('hidden')) {
                    if (viewerUrl) {
                        reviewPreviewFrame.src = viewerUrl;
                        reviewPreviewWrap.classList.remove('hidden');
                    } else {
                        alert('Preview is not available for this file type.');
                    }
                } else {
                    reviewPreviewWrap.classList.add('hidden');
                    reviewPreviewFrame.src = 'about:blank';
                }
            });
        }
    }

    // Upload new
    if (upOpen) upOpen.addEventListener('click', () => {
        mTitle.textContent = 'Upload document';
        form.reset();
        $('doc_id').value = '';
        fileRow.classList.remove('hidden');
        open(modal);
    });
    cancel.addEventListener('click', () => close(modal));
    reviewCancel.addEventListener('click', () => close(reviewModal));


    // Save (create or edit)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        const id = fd.get('id');
        if (!fd.get('title')) return alert('Title is required.');
        if (!id && !fd.get('file').name) return alert('File is required.');
        const res = await fetch(`${API}?action=save`, { method: 'POST', body: fd });
        const json = await res.json();
        if (!json.ok) return alert(json.error || 'Save failed.');
        close(modal);
        load().catch(console.error);
    });

    // Share
    shareCancel.addEventListener('click', () => close(shareModal));
    shareForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            document_id: Number(document.getElementById('share_doc_id').value),
            user_id: Number(document.getElementById('share_user_id').value)
        };
        if (!payload.document_id || !payload.user_id) return;
        const res = await fetch(`${API}?action=share`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!json.ok) return alert(json.error || 'Share failed.');
        close(shareModal);
        if (currentTab === 'shared') load().catch(console.error);
    });

    // Review submit
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            document_id: Number(document.getElementById('review_doc_id').value),
            rating: Number(document.getElementById('review_rating').value),
            comment: document.getElementById('review_comment').value.trim()
        };
        if (!payload.document_id || !payload.rating || payload.rating < 1 || payload.rating > 5) {
            return alert('Please provide a rating from 1 to 5.');
        }
        
        const res = await fetch(`${API}?action=review`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!json.ok) return alert(json.error || 'Review failed.');
        close(reviewModal);
        load().catch(console.error);
    });

    // Search
    search.addEventListener('input', () => {
        console.log('Search input event fired. Value:', search.value); 
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            console.log('Debounce finished. Calling load()'); 
            load().catch(console.error);
        }, 250);
    });

    // Tabs
    tabMine.addEventListener('click', () => setTab('mine'));
    tabShared.addEventListener('click', () => setTab('shared'));

    // init
    setTab('mine');
})();