// app/js/auth.js
(function(){
  
  // ===== 1. ORIGINAL PASSWORD TOGGLE (Pinanatili) =====
  function toggle(input, icon){
    if (!input) return;
    if (input.type === 'password'){
      input.type = 'text';
      icon && icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
      input.type = 'password';
      icon && icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
  }

  function initToggles(){
    document.querySelectorAll('[data-toggle="password"]').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetSel = btn.getAttribute('data-target');
        const iconSel   = btn.getAttribute('data-icon');
        const input = targetSel ? document.querySelector(targetSel) : null;
        const icon  = iconSel ? document.querySelector(iconSel) : btn.querySelector('i');
        toggle(input, icon);
      });
    });
  }

  // ===== 2. ORIGINAL LOGIN UX (Pinanatili) =====
  function initLoginUX(){
    const form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', () => {
      const btn = form.querySelector('button[type="submit"]');
      if (btn){ btn.disabled = true; btn.textContent = 'Signing in...'; }
    });
  }

  // ===== 3. MODIFIED SIGNUP CHECKS (In-update ang password rule) =====
  function initSignupChecks(){
    const form = document.getElementById('signupForm');
    if (!form) return;
    
    // Ito na yung bagong strong password regex
    const strongPasswordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
    
    form.addEventListener('submit', (e) => {
      const pwd   = document.getElementById('password')?.value || '';
      const cpwd  = document.getElementById('confirm_password')?.value || '';
      const email = document.getElementById('email')?.value || '';
      const terms = document.getElementById('terms');

      // Pinalitan ang 'pwd.length < 8' ng bagong regex check
      if (!strongPasswordRegex.test(pwd)){ 
        e.preventDefault(); 
        alert('Password must be 8+ chars and include uppercase, lowercase, number, and special character.'); 
        return; 
      }
      
      // Pinanatili ang ibang checks
      if (pwd !== cpwd){ e.preventDefault(); alert('Passwords did not match.'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){ e.preventDefault(); alert('Please enter a valid email.'); return; }
      if (terms && !terms.checked){ e.preventDefault(); alert('Please accept the Terms.'); return; }

      // Pinanatili ang button disable logic
      const btn = form.querySelector('button[type="submit"]');
      if (btn){ btn.disabled = true; btn.textContent = 'Creating account...'; }
    });
  }
  
  // ===== 4. BAGONG Idinagdag: REAL-TIME PASSWORD STRENGTH CHECK =====
  function initPasswordStrengthCheck() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) return; // Tatakbo lang kung nasa signup page

    const reqs = {
      length: document.getElementById('req-length'),
      lower: document.getElementById('req-lower'),
      upper: document.getElementById('req-upper'),
      number: document.getElementById('req-number'),
      special: document.getElementById('req-special')
    };
    
    // Check kung may checklist sa page
    if (Object.values(reqs).some(el => !el)) return;

    const validators = {
      length: (val) => val.length >= 8,
      lower: (val) => /[a-z]/.test(val),
      upper: (val) => /[A-Z]/.test(val),
      number: (val) => /[0-9]/.test(val),
      special: (val) => /[^A-Za-z0-9]/.test(val) // Simpleng non-alphanumeric
    };

    const updateRequirement = (reqElement, isValid) => {
      const icon = reqElement.querySelector('.req-icon');
      const text = reqElement.querySelector('span');
      
      if (isValid) {
        icon.classList.remove('fa-circle-xmark', 'text-red-500');
        icon.classList.add('fa-circle-check', 'text-green-500');
        text.classList.add('text-gray-700');
      } else {
        icon.classList.remove('fa-circle-check', 'text-green-500');
        icon.classList.add('fa-circle-xmark', 'text-red-500');
        text.classList.remove('text-gray-700');
      }
    };

    passwordInput.addEventListener('input', () => {
      const value = passwordInput.value;
      updateRequirement(reqs.length, validators.length(value));
      updateRequirement(reqs.lower, validators.lower(value));
      updateRequirement(reqs.upper, validators.upper(value));
      updateRequirement(reqs.number, validators.number(value));
      updateRequirement(reqs.special, validators.special(value));
    });
  }

  // ===== 5. BAGONG Idinagdag: REAL-TIME PASSWORD MATCH CHECK =====
  function initPasswordMatchCheck() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const confirmMsg = document.getElementById('confirm-pass-msg');

    if (!passwordInput || !confirmPasswordInput || !confirmMsg) return; // Tatakbo lang kung nasa signup page

    const checkMatch = () => {
      const passVal = passwordInput.value;
      const confirmVal = confirmPasswordInput.value;

      if (!confirmVal) {
        confirmMsg.textContent = '';
      } else if (passVal === confirmVal) {
        confirmMsg.textContent = 'Passwords match!';
        confirmMsg.classList.remove('text-red-600');
        confirmMsg.classList.add('text-green-600');
      } else {
        confirmMsg.textContent = 'Passwords do not match.';
        confirmMsg.classList.remove('text-green-600');
        confirmMsg.classList.add('text-red-600');
      }
    };

    passwordInput.addEventListener('input', checkMatch);
    confirmPasswordInput.addEventListener('input', checkMatch);
  }
  
  // ===== 6. MODIFIED INIT FUNCTION (Idinagdag ang mga bago) =====
  function init(){
    initToggles();
    initLoginUX();
    initSignupChecks();
    initPasswordStrengthCheck(); // <-- BAGONG TAWAG
    initPasswordMatchCheck();  // <-- BAGONG TAWAG
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();