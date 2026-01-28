/* app/js/dashboard.js
   Lightweight behaviors for the dashboard page:
   - Announcement modal open/close
   - Close on ESC or backdrop click
   - Demo submit handler
   - Simple tabs state
*/
(function () {
  const openBtn  = document.getElementById('announcementOpenBtn');
  const closeBtn = document.getElementById('announcementCloseBtn');
  const modal    = document.getElementById('announcementModal');
  const form     = document.getElementById('announcementForm');

  function open()  { modal && modal.classList.remove('hidden'); }
  function close() { modal && modal.classList.add('hidden'); }

  openBtn && openBtn.addEventListener('click', open);
  closeBtn && closeBtn.addEventListener('click', close);

  // Close when clicking on the dark backdrop (uses data-close="true")
  modal && modal.addEventListener('click', (e) => {
    if (e.target && e.target.dataset && e.target.dataset.close === 'true') close();
  });

  // Close on ESC
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });

  // Demo submission — replace with your AJAX/POST if needed
  form && form.addEventListener('submit', (e) => {
    e.preventDefault();
    // TODO: send to server
    close();
  });

  // Tabs toggle (purely visual)
  document.querySelectorAll('.tabs .tab').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.parentElement.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      btn.classList.add('active');
    });
  });
})();
