/* assets/js/confirm.js - opens the generic confirmation dialog.
   Button example:
   <button data-confirm data-title="Deactivate?" data-text="..." data-button="Deactivate"
           data-tone="danger" data-action="/url" data-fields='{"id":1}'> */
(function () {
  'use strict';
  var modal = document.getElementById('confirmModal');
  if (!modal) return;

  var form = modal.querySelector('form');
  var fields = modal.querySelector('[data-confirm-fields]');
  var icons = { danger: 'bi-exclamation-triangle', warning: 'bi-exclamation-circle', success: 'bi-check2-circle' };

  document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tone = btn.getAttribute('data-tone') || 'success';
      form.setAttribute('action', btn.getAttribute('data-action') || '');

      fields.innerHTML = '';
      var data = {};
      try { data = JSON.parse(btn.getAttribute('data-fields') || '{}'); } catch (e) {}
      Object.keys(data).forEach(function (k) {
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = k; input.value = data[k];
        fields.appendChild(input);
      });

      document.getElementById('cmTitle').textContent = btn.getAttribute('data-title') || 'Are you sure?';
      document.getElementById('cmText').textContent = btn.getAttribute('data-text') || '';

      var icon = document.getElementById('cmIcon');
      icon.className = 'df-modal-icon df-modal-icon-' + tone;
      icon.innerHTML = '<i class="bi ' + (icons[tone] || 'bi-question-lg') + '"></i>';

      var ok = document.getElementById('cmButton');
      ok.textContent = btn.getAttribute('data-button') || 'Confirm';
      ok.className = 'df-btn ' + (tone === 'danger' ? 'df-btn-danger' : 'df-btn-primary');

      modal.classList.add('is-open');
      document.body.classList.add('df-no-scroll');
    });
  });
})();