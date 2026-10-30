/* assets/js/auth.js - small helpers for the login / change-password pages */
(function () {
  // Show / hide password
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.querySelector(btn.getAttribute('data-toggle-password'));
      var icon = btn.querySelector('i');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  // Prevent double-submits and show that something is happening
  document.querySelectorAll('form[data-loading-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.classList.add('is-loading');
        btn.querySelector('.df-btn-label').textContent = 'Please wait...';
        setTimeout(function () { btn.disabled = true; }, 0);   // disable after the form data is sent
      }
    });
  });
})();