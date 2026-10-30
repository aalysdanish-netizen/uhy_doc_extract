/* assets/js/users.js - user management pages */
(function () {
  'use strict';

  /* ---------- Confirmation modal (activate / deactivate / reset) ---------- */
  var modal = document.getElementById('userActionModal');
  if (modal) {
    var pw = modal.getAttribute('data-default-password');
    var config = {
      deactivate: {
        title: 'Deactivate this user?', icon: 'bi-person-x', tone: 'danger', button: 'Deactivate', btnClass: 'df-btn-danger',
        text: function (n) { return n + ' will no longer be able to sign in. Their documents and history are kept.'; }
      },
      activate: {
        title: 'Activate this user?', icon: 'bi-person-check', tone: 'success', button: 'Activate', btnClass: 'df-btn-primary',
        text: function (n) { return n + ' will be able to sign in again.'; }
      },
      reset_password: {
        title: 'Reset password?', icon: 'bi-key', tone: 'warning', button: 'Reset Password', btnClass: 'df-btn-primary',
        text: function (n) { return n + "'s password will be reset to " + pw + ' and they must choose a new one at next sign in.'; }
      }
    };

    document.querySelectorAll('[data-user-action]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var c = config[btn.getAttribute('data-user-action')];
        if (!c) return;
        document.getElementById('uamUserId').value = btn.getAttribute('data-user-id');
        document.getElementById('uamAction').value = btn.getAttribute('data-user-action');
        document.getElementById('uamTitle').textContent = c.title;
        document.getElementById('uamText').textContent = c.text(btn.getAttribute('data-user-name'));

        var icon = document.getElementById('uamIcon');
        icon.className = 'df-modal-icon df-modal-icon-' + c.tone;
        icon.innerHTML = '<i class="bi ' + c.icon + '"></i>';

        var confirmBtn = document.getElementById('uamConfirm');
        confirmBtn.className = 'df-btn ' + c.btnClass;
        confirmBtn.textContent = c.button;

        modal.classList.add('is-open');
        document.body.classList.add('df-no-scroll');
      });
    });
  }

  /* ---------- Create / edit form ---------- */
  var role = document.querySelector('[data-role-select]');
  var staffTypeField = document.querySelector('[data-staff-type-field]');
  if (role && staffTypeField) {
    var sync = function () { staffTypeField.hidden = role.value !== 'staff'; };
    role.addEventListener('change', sync);
    sync();
  }

  // Live preview of the login email
  var userInput = document.querySelector('[data-username-input]');
  var preview = document.querySelector('[data-email-preview]');
  if (userInput && preview) {
    var domain = preview.textContent.split('@')[1];
    var update = function () {
      var v = userInput.value.trim().toLowerCase().split('@')[0] || 'username';
      preview.textContent = v + '@' + domain;
    };
    userInput.addEventListener('input', update);
    update();
  }
})();