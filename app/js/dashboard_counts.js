// app/js/dashboard_counts.js — populates KPI cards with live counts (updated)
(async () => {
  try {
    const r = await fetch('dashboard_api.php', { credentials: 'same-origin' });
    const j = await r.json();
    if (!j.ok) throw new Error(j.error || 'Failed to load counts');
    const { facilities = 0, programs = 0, visits = 0, users = 0 } = j.data || {};

    const set = (id, v) => {
      const el = document.getElementById(id);
      if (el) el.textContent = v;
    };

    // Accredited
    set('kpiFacilities', facilities);

    // Programs
    set('kpiPrograms', programs);

    // Visits
    set('kpiVisits', visits);

    // Users
    set('kpiUsers', users);
  } catch (e) {
    console.warn('KPI load error:', e);
  }
})();
