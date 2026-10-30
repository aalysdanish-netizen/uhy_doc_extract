/* assets/js/batch.js - Extract + Copy for every document in a batch */
(function () {
  'use strict';
  var root = document.getElementById('batchRoot');
  if (!root) return;

  var base = (document.querySelector('meta[name="df-base"]') || {}).content || '';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var panels = JSON.parse(root.getAttribute('data-panels'));

  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

  function post(url, data) {
    var fd = new FormData();
    Object.keys(data || {}).forEach(function (k) { if (data[k] !== null) fd.append(k, data[k]); });
    fd.append('csrf_token', csrf);
    return fetch(base + url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.text().then(function (t) {
        var d = null; try { d = JSON.parse(t); } catch (e) {}
        return d || { ok: false, error: 'Unexpected server response.' };
      }); })
      .catch(function () { return { ok: false, error: 'Network error.' }; });
  }

  var STEPS = ['Reading the image…', 'Recognising text…', 'Splitting into fields…'];

  function runLoading(card, promise) {
    var box = card.querySelector('[data-loading]');
    var bar = card.querySelector('[data-loading-bar]');
    var text = card.querySelector('[data-loading-text]');
    box.hidden = false;
    var i = 0, pct = 0;
    text.textContent = STEPS[0];
    var timer = setInterval(function () {
      pct = Math.min(92, pct + 12);
      bar.style.width = pct + '%';
      if (pct > 30 * (i + 1) && i < STEPS.length - 1) { i++; text.textContent = STEPS[i]; }
    }, 260);
    return promise.then(function (res) {
      clearInterval(timer);
      bar.style.width = '100%';
      setTimeout(function () { box.hidden = true; bar.style.width = '0%'; }, 300);
      return res;
    });
  }

  function renderCopyBox(card, rawText) {
    var box = card.querySelector('[data-copybox]');
    box.innerHTML = '';
    if (!rawText) return;
    var wrap = document.createElement('div');
    wrap.className = 'df-copybox-inner';
    wrap.innerHTML = '<textarea class="df-input df-copybox-textarea" rows="7" readonly></textarea>'
      + '<button type="button" class="df-btn df-btn-primary df-btn-sm df-copy-btn"><i class="bi bi-clipboard"></i> Copy to Clipboard</button>';
    wrap.querySelector('textarea').value = rawText;
    var btn = wrap.querySelector('.df-copy-btn');
    var defaultLabel = btn.innerHTML;
    btn.addEventListener('click', function () {
      var done = function () { btn.innerHTML = '<i class="bi bi-check2-circle"></i> Copied!'; setTimeout(function () { btn.innerHTML = defaultLabel; }, 1500); };
      navigator.clipboard && navigator.clipboard.writeText ? navigator.clipboard.writeText(rawText).then(done) : (function () {
        var t = wrap.querySelector('textarea'); t.select(); document.execCommand('copy'); done();
      })();
    });
    box.appendChild(wrap);
  }

  function renderFields(card, fields, extractionId) {
    var wrap = card.querySelector('[data-fields]');
    wrap.innerHTML = '';
    if (!fields || !fields.length) return;
    var details = document.createElement('details');
    details.className = 'df-fields-toggle';
    details.innerHTML = '<summary>Break into labeled fields <span class="df-muted">(optional)</span></summary>';
    var list = document.createElement('div');
    list.className = 'df-ocr-fields';
    fields.forEach(function (f) {
      var row = document.createElement('div');
      row.className = 'df-ocr-field';
      row.innerHTML = '<div class="df-ocr-field-inputs"><input class="df-input" type="text" value="' + esc(f.label) + '" readonly>'
        + '<input class="df-input" type="text" value="' + esc(f.value || '') + '" readonly></div>';
      list.appendChild(row);
    });
    details.appendChild(list);
    wrap.appendChild(details);
  }

  function extractCard(card, versionId) {
    return runLoading(card, post('/api/ocr-run.php', { version_id: versionId })).then(function (res) {
      if (res.ok) {
        renderCopyBox(card, res.raw_text);
        renderFields(card, res.fields, res.extraction_id);
        var btn = card.querySelector('.df-extract-btn');
        if (btn) btn.remove();
      } else {
        alert(res.error || 'Could not process this document.');
      }
      return res;
    });
  }

  panels.forEach(function (p) {
    var card = document.querySelector('[data-batch-card="' + p.documentId + '"]');
    if (!card) return;

    if (p.extractionId) {
      renderCopyBox(card, p.rawText);
      renderFields(card, p.fields, p.extractionId);
    } else {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'df-btn df-btn-primary df-btn-block df-extract-btn';
      btn.innerHTML = p.isImage ? '<i class="bi bi-magic"></i> Extract Information' : '<i class="bi bi-pencil"></i> Enter by Hand';
      btn.addEventListener('click', function () { btn.disabled = true; extractCard(card, p.versionId); });
      card.querySelector('[data-copybox]').before(btn);
    }
  });

  var btnAll = document.getElementById('btnExtractAll');
  if (btnAll) {
    btnAll.addEventListener('click', function () {
      btnAll.disabled = true;
      var chain = Promise.resolve();
      panels.forEach(function (p) {
        if (p.extractionId) return;
        var card = document.querySelector('[data-batch-card="' + p.documentId + '"]');
        chain = chain.then(function () { return extractCard(card, p.versionId); });
      });
      chain.finally(function () { btnAll.disabled = false; });
    });
  }
})();