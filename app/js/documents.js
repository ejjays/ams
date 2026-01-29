(function () {
  const API = 'documents_api.php';
  const PUBLIC_VIEWER = 'public_view.php';

  const tabMine = document.getElementById('tabMine');
  const tabShared = document.getElementById('tabShared');
  const docList = document.getElementById('docList');
  const search = document.getElementById('docSearch');

  const upOpen = document.getElementById('docUploadOpen');
  const modal = document.getElementById('docModal');
  const mTitle = document.getElementById('docModalTitle');
  const form = document.getElementById('docForm');
  const fileRow = document.getElementById('fileRow');
  const titleInput = document.getElementById('title');
  const titleCount = document.getElementById('titleCount');

  if (titleInput && titleCount) {
    titleInput.addEventListener('input', () => {
      const len = titleInput.value.length;
      titleCount.textContent = `${len} / 60`;
      titleCount.classList.toggle('text-blue-600', len > 50);
    });
  }

  const shareModal = document.getElementById('shareModal');
  const shareForm = document.getElementById('shareForm');

  // Review modal refs
  const reviewModal = document.getElementById('reviewModal');
  const reviewForm = document.getElementById('reviewForm');
  const reviewTogglePreview = document.getElementById('reviewTogglePreview');
  const reviewPreviewWrap = document.getElementById('reviewPreviewWrap');
  const reviewPreviewFrame = document.getElementById('reviewPreviewFrame');

  let currentTab = 'mine';
  let debounce;

  const $ = id => document.getElementById(id);

  function showToast(msg, type = 'info') {
    const container = $('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    const colors = {
      success: 'bg-emerald-600 shadow-emerald-200',
      error: 'bg-rose-600 shadow-rose-200',
      info: 'bg-indigo-600 shadow-indigo-200'
    };
    const icons = {
      success: 'fa-circle-check',
      error: 'fa-circle-exclamation',
      info: 'fa-circle-info'
    };

    toast.className = `flex items-center gap-3 px-6 py-3.5 rounded-2xl text-white text-sm font-bold shadow-xl transition-all duration-500 translate-y-[-20px] opacity-0 ${colors[type] || colors.info}`;
    toast.style.pointerEvents = 'auto';
    toast.innerHTML = `<i class="fa-solid ${icons[type] || icons.info}"></i> <span>${msg}</span>`;

    container.appendChild(toast);

    // Trigger Entrance
    setTimeout(() => {
      toast.classList.remove('translate-y-[-20px]', 'opacity-0');
    }, 10);

    // Auto Remove
    setTimeout(() => {
      toast.classList.add('translate-y-[-20px]', 'opacity-0');
      setTimeout(() => toast.remove(), 500);
    }, 4000);
  }

  function showModal(el) {
    if (!el) return;
    el.classList.remove('hidden', 'closing');
    el.style.display = 'flex';
  }
  function hideModal(el) {
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
      if (m) hideModal(m);
    }
    const closer = e.target.closest('[data-close="true"]');
    if (closer) {
      const m = closer.closest('.modal');
      if (m) hideModal(m);
    }
  });

  window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal').forEach(hideModal);
    }
  });

  function extBadge(ext) {
    return `<span class="text-sm px-2.5 py-1.5 rounded bg-slate-100 text-slate-700 border">${
      ext || ''
    }</span>`;
  }

  function esc(s) {
    return (s || '').replace(
      /[&<>"']/g,
      m =>
        ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        })[m]
    );
  }

  function stars(n) {
    if (!n || isNaN(n)) return '';
    const full = '★'.repeat(Math.round(n));
    const empty = '☆'.repeat(5 - Math.round(n));
    return `<span class="text-amber-500">${full}</span><span class="text-slate-300">${empty}</span><span class="ml-1 text-xs text-slate-500">(${Number(
      n
    ).toFixed(1)})</span>`;
  }

  function card(d) {
    const title = esc(d.title || '(untitled)');
    const cmt = esc(d.comment || '');
    const ext = esc((d.file_ext || '').toUpperCase());
    const owner = d.owner
      ? `<div class="text-[11px] font-medium text-slate-400 mt-1">by ${esc(d.owner)}</div>`
      : '';
    const aiTag = d.ai_tag
      ? `<span class="text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 px-3 py-1 rounded-lg inline-flex items-center border border-indigo-100 mb-3" title="${esc(d.ai_tag)}">
            <i class="fa-solid fa-tag mr-1.5 text-[9px]"></i> 
            ${esc(d.ai_tag.includes(':') ? d.ai_tag.split(':')[1].trim() : d.ai_tag)}
          </span>`
      : `<button data-re-tag="${d.id}" class="h-7 mb-3 flex items-center gap-2 px-3 rounded-lg border border-dashed border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all text-[9px] font-bold uppercase tracking-widest group">
            <i class="fa-solid fa-rotate-right group-active:rotate-180 transition-transform duration-500"></i>
            <span>Suggest Tag</span>
         </button>`;

    const ratingLine =
      d.avg_rating || d.my_review_rating
        ? `<div class="mt-2 text-xs">${stars(d.my_review_rating || d.avg_rating)}</div>`
        : '';

    const controls = `
          <button class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Edit" data-edit='${JSON.stringify(d)}'><i class="fa-solid fa-pen text-[10px]"></i></button>
          <button class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Share" data-share="${d.id}"><i class="fa-solid fa-share-nodes text-[10px]"></i></button>
          <button class="w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Review" data-review="${d.id}" data-stored-name="${esc(d.stored_name)}" data-file-ext="${esc(d.file_ext)}"><i class="fa-solid fa-star text-[10px]"></i></button>
          <button class="inline-flex items-center gap-2 px-3 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors text-[10px] font-bold uppercase tracking-wider" title="AI Insight" data-ai-insight="${d.id}">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Analyze</span>
          </button>`;

    return `
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-200 p-5 flex flex-col h-full">
      <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex-1 min-w-0">
          ${aiTag}
          <div class="font-bold text-slate-900 leading-snug text-lg">${title}</div>
          ${cmt ? `<div class="text-xs text-slate-500 mt-1 line-clamp-1">${cmt}</div>` : ''}
          ${owner}
          ${ratingLine}
        </div>
        <div class="flex-shrink-0">
            <div class="px-2 py-1 rounded bg-slate-100 text-slate-600 border border-slate-200 font-bold text-[9px]">
                ${ext}
            </div>
        </div>
      </div>
      
      <div class="mt-auto pt-4 flex items-center justify-start border-t border-slate-100">
        <div class="flex items-center gap-1.5">
            <a class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Download" href="${API}?action=download&id=${d.id}">
              <i class="fa-solid fa-download text-[10px]"></i>
            </a>
            ${controls}
        </div>
      </div>
    </div>`;
  }

  async function load() {
    const q = search.value.trim();
    console.log(`Loading... Tab: ${currentTab}, Query: "${q}"`);
    const res = await fetch(
      `${API}?action=list&tab=${currentTab}&q=${encodeURIComponent(q)}`
    );

    if (!res.ok) {
      docList.innerHTML = `<div class="text-slate-500">Error: ${res.statusText}</div>`;
      return;
    }

    const json = await res.json();

    if (!json.ok) {
      docList.innerHTML = `<div class="text-slate-500">${esc(
        json.error || 'Failed to load data.'
      )}</div>`;
      return;
    }

    const list = json.data || [];
    docList.innerHTML = list.length
      ? list.map(card).join('')
      : `<div class="text-slate-500">No documents.</div>`;
    bindCardEvents();
  }

  function setTab(tab) {
    currentTab = tab;
    const active = 'bg-white text-blue-700 shadow-sm';
    const inactive = 'text-slate-500 hover:text-slate-700';

    if (tab === 'mine') {
      tabMine.className = `px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 ${active}`;
      tabShared.className = `px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 ${inactive}`;
    } else {
      tabShared.className = `px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 ${active}`;
      tabMine.className = `px-6 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest transition-all duration-300 ${inactive}`;
    }
    load().catch(console.error);
  }

  function bindCardEvents() {
    // edit
    docList.querySelectorAll('[data-edit]').forEach(btn => {
      btn.addEventListener('click', () => {
        const d = JSON.parse(btn.getAttribute('data-edit'));
        mTitle.textContent = 'Edit Document';
        if ($('docSubmitBtn')) $('docSubmitBtn').textContent = 'Update Document';
        $('doc_id').value = d.id;
        $('title').value = d.title || '';
        $('comment').value = d.comment || '';
        $('file').value = '';
        fileRow.classList.remove('hidden');
        showModal(modal);
      });
    });
    // share
    docList.querySelectorAll('[data-share]').forEach(btn => {
      btn.addEventListener('click', () => {
        $('share_doc_id').value = btn.getAttribute('data-share');
        $('share_user_id').value = '';
        showModal(shareModal);
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

        showModal(reviewModal);
      });
    });

    // --- NEW: AI Insight Modal Handler ---
    let typingTimer;
    const openAIInsight = rawText => {
      const aiModal = $('aiInsightModal');
      const content = $('aiInsightContent');
      content.innerHTML = '';
      clearTimeout(typingTimer);
      showModal(aiModal);

      // Simple Markdown Parser (Bold & Newlines)
      const formatted = rawText
        .replace(
          /\*\*(.*?)\*\*/g,
          '<strong class="text-indigo-900 font-black">$1</strong>'
        )
        .replace(/\n/g, '<br/>');

      let i = 0;
      let currentHtml = '';
      const type = () => {
        if (i < formatted.length) {
          if (formatted[i] === '<') {
            const tagEnd = formatted.indexOf('>', i);
            currentHtml += formatted.substring(i, tagEnd + 1);
            i = tagEnd + 1;
          } else {
            currentHtml += formatted[i];
            i++;
          }
          content.innerHTML = currentHtml;
          content.scrollTop = content.scrollHeight;
          typingTimer = setTimeout(type, 5);
        }
      };
      type();
    };

    // AI Insight
    docList.querySelectorAll('[data-ai-insight]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-ai-insight');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        try {
          console.log('🤖 AI: Fetching document insight for ID:', id);
          const res = await fetch(`${API}?action=ai_insight&id=${id}`);
          const json = await res.json();

          if (json.ok && json.data.insight) {
            console.log(
              `🤖 AI: Insight received via ${
                json.data.model || 'Unknown Model'
              }`
            );
            if (
              json.data.insight.startsWith('AI Error:') ||
              json.data.insight.startsWith('Connection Error:')
            ) {
              console.error('🤖 AI Error (Gemini):', json.data.insight);
            }
            openAIInsight(json.data.insight);
          } else {
            console.warn(
              '🤖 AI: No specific insight returned or analysis failed.'
            );
            openAIInsight(
              'The AI is currently analyzing this document. Please ensure the file contains readable text.'
            );
          }
        } catch (e) {
          console.error('🤖 AI: Network or Connection Failure:', e);
          showToast('Failed to connect to AI service.');
        } finally {
          btn.disabled = false;
          btn.innerHTML = originalHtml;
        }
      });
    });

    // AI Re-tag (Manual Trigger)
    docList.querySelectorAll('[data-re-tag]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-re-tag');
        btn.disabled = true;
        const icon = btn.querySelector('i');
        if (icon) icon.classList.add('fa-spin');
        
        try {
          const res = await fetch(`${API}?action=re_tag&id=${id}`);
          const json = await res.json();
          if (json.ok) {
            showToast("AI successfully categorized this document!", "success");
            load().catch(console.error);
          } else {
            showToast(json.error || "AI could not find a suitable tag.", "info");
          }
        } catch (e) {
          showToast("Connection to AI service failed.", "error");
        } finally {
          btn.disabled = false;
          if (icon) icon.classList.remove('fa-spin');
        }
      });
    });

    // Download Toast
    docList.querySelectorAll('a[title="Download"]').forEach(link => {
      link.addEventListener('click', () => {
        showToast("Your download has started!", "success");
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

        const relativeFileUrl = `${PUBLIC_VIEWER}?file=${encodeURIComponent(
          storedName
        )}`;
        const fileUrl = new URL(relativeFileUrl, window.location.href).href;

        if (nativeExts.includes(fileExt)) {
          viewerUrl = fileUrl;
        } else if (officeExts.includes(fileExt)) {
          viewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(
            fileUrl
          )}`;
        } else {
          viewerUrl = '';
        }

        if (reviewPreviewWrap.classList.contains('hidden')) {
          if (viewerUrl) {
            reviewPreviewFrame.src = viewerUrl;
            reviewPreviewWrap.classList.remove('hidden');
          } else {
            showToast('Preview is not available for this file type.');
          }
        } else {
          reviewPreviewWrap.classList.add('hidden');
          reviewPreviewFrame.src = 'about:blank';
        }
      });
    }
  }

  // Upload new
  if (upOpen)
    upOpen.addEventListener('click', () => {
      mTitle.textContent = 'Upload Document';
      if ($('docSubmitBtn')) $('docSubmitBtn').textContent = 'Save Document';
      form.reset();
      $('doc_id').value = '';
      fileRow.classList.remove('hidden');
      showModal(modal);
    });

  // Save (create or edit)
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(form);
    const id = fd.get('id');
    if (!fd.get('title')) return showToast('Title is required.');
    if (!id && !fd.get('file').name) return showToast('File is required.');
    const res = await fetch(`${API}?action=save`, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return showToast(json.error || 'Save failed.');
    hideModal(modal);
    load().catch(console.error);
  });

  // Share
  shareForm.addEventListener('submit', async e => {
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
    if (!json.ok) return showToast(json.error || 'Share failed.');
    hideModal(shareModal);
    if (currentTab === 'shared') load().catch(console.error);
  });

  // Review submit
  reviewForm.addEventListener('submit', async e => {
    e.preventDefault();
    const payload = {
      document_id: Number(document.getElementById('review_doc_id').value),
      rating: Number(document.getElementById('review_rating').value),
      comment: document.getElementById('review_comment').value.trim()
    };
    if (
      !payload.document_id ||
      !payload.rating ||
      payload.rating < 1 ||
      payload.rating > 5
    ) {
      return showToast('Please provide a rating from 1 to 5.');
    }

    const res = await fetch(`${API}?action=review`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!json.ok) return showToast(json.error || 'Review failed.');
    hideModal(reviewModal);
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
