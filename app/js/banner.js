// app/js/banner.js
(function(){
  function autoDismissEl(el, ms){
    setTimeout(() => {
      el.style.transition = 'opacity .35s ease, transform .35s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-6px)';
      setTimeout(() => el.remove(), 380);
    }, ms);
  }
  function init(){
    // support either a single #page-banner or multiple [data-autodismiss]
    const single = document.getElementById('page-banner');
    if (single && !single.dataset.autodismiss) {
      autoDismissEl(single, 3000);
    }
    document.querySelectorAll('[data-autodismiss]').forEach(el => {
      const ms = parseInt(el.getAttribute('data-autodismiss'), 10) || 3000;
      autoDismissEl(el, ms);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
