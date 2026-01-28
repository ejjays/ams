
// app/js/sidebar.js
(function () {

  // Create a floating burger button that remains visible when sidebar is collapsed
  function ensureFloatingButton() {
    var fab = document.getElementById('sidebarToggleFloating');
    if (!fab) {
      fab = document.createElement('button');
      fab.id = 'sidebarToggleFloating';
      fab.className = 'sidebar-fab';
      // Use Font Awesome if available; fallback to text
      fab.innerHTML = '<i class="fa-solid fa-bars" aria-hidden="true"></i><span class="sr-only">Toggle sidebar</span>';
      document.body.appendChild(fab);
    }
    fab.addEventListener('click', function () {
      var nowCollapsed = !document.body.classList.contains('sidebar-collapsed');
      applyState(nowCollapsed);
      store(nowCollapsed);
    });
  }

  function applyState(collapsed) {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    var btn = document.getElementById('sidebarToggle');
    var aside = document.getElementById('sidebar');
    if (btn) btn.setAttribute('aria-expanded', String(!collapsed));
    if (aside) aside.setAttribute('aria-hidden', String(collapsed));
  }

  function getStored() {
    try { return localStorage.getItem('sidebarCollapsed') === '1'; }
    catch (_) { return false; }
  }

  function store(collapsed) {
    try { localStorage.setItem('sidebarCollapsed', collapsed ? '1' : '0'); }
    catch (_) {}
  }

  document.addEventListener('DOMContentLoaded', function () {
    ensureFloatingButton();
    // Initialize from storage
    applyState(getStored());

    var btn = document.getElementById('sidebarToggle');
    if (!btn) return;
    btn.addEventListener('click', function () {
      var nowCollapsed = !document.body.classList.contains('sidebar-collapsed');
      applyState(nowCollapsed);
      store(nowCollapsed);
    });
  });
})();
