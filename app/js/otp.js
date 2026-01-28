// app/js/otp.js
(function(){
  // DEV banner countdown
  const exp = document.getElementById('dev-exp');
  if (exp){
    let secs = parseInt(exp.getAttribute('data-secs') || '0', 10);
    const tick = () => {
      if (isNaN(secs)) return;
      if (secs <= 0) { exp.textContent = 'expired'; return; }
      exp.textContent = 'expires in ' + (secs--) + 's';
      setTimeout(tick, 1000);
    };
    tick();
  }
})();
