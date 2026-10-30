/* assets/js/chat-common.js - shared by community.js and personal-chat.js */
window.DFChat = (function () {
  var base = (document.querySelector('meta[name="df-base"]') || {}).content || '';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  function post(url, formData) {
    formData.append('csrf_token', csrf);
    return fetch(base + url, { method: 'POST', body: formData, credentials: 'same-origin' })
      .then(function (r) { return r.text().then(function (t) {
        var d = null; try { d = JSON.parse(t); } catch (e) {}
        return d || { ok: false, error: 'Unexpected server response.' };
      }); })
      .catch(function () { return { ok: false, error: 'Network error.' }; });
  }

  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

  function wireAttachPreview(form) {
    var input = form.querySelector('input[type="file"]');
    var list = form.querySelector('.df-attach-list');
    if (!input || !list) return;
    input.addEventListener('change', function () {
      list.textContent = input.files.length ? input.files.length + ' file' + (input.files.length > 1 ? 's' : '') + ' attached' : '';
    });
  }

  return { post: post, esc: esc, wireAttachPreview: wireAttachPreview, base: base };
})();