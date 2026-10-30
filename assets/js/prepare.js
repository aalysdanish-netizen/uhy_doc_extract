/* assets/js/prepare.js - single-document workspace: Extract + Copy + Save Version */
(function () {
  'use strict';

  var root = document.getElementById('prepareRoot');
  if (!root) return;

  var base = (document.querySelector('meta[name="df-base"]') || {}).content || '';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var state = JSON.parse(root.getAttribute('data-bootstrap'));

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }

  function post(url, data) {
    var fd = new FormData();
    Object.keys(data || {}).forEach(function (k) { if (data[k] !== null) fd.append(k, data[k]); });
    fd.append('csrf_token', csrf);
    return fetch(base + url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.text().then(function (t) {
        var d = null; try { d = JSON.parse(t); } catch (e) {}
        return d || { ok: false, error: 'Unexpected server response (HTTP ' + r.status + ').' };
      }); })
      .catch(function () { return { ok: false, error: 'Network error. Please try again.' }; });
  }

  var STEPS = ['Reading the image…', 'Recognising text…', 'Splitting into fields…'];

  function runLoading(promise) {
    var box = $('[data-loading="current"]');
    var bar = $('[data-loading-bar]', box);
    var text = $('[data-loading-text]', box);
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

  function renderCopyBox(rawText) {
    var box = $('[data-copybox="current"]');
    box.innerHTML = '';
    if (!rawText) return;
    var wrap = document.createElement('div');
    wrap.className = 'df-copybox-inner';
    wrap.innerHTML = '<label class="df-label">Extracted Text</label>'
      + '<textarea class="df-input df-copybox-textarea" rows="8" readonly></textarea>'
      + '<button type="button" class="df-btn df-btn-primary df-btn-sm df-copy-btn"><i class="bi bi-clipboard"></i> Copy to Clipboard</button>';
    wrap.querySelector('textarea').value = rawText;
    var btn = wrap.querySelector('.df-copy-btn');
    var defaultLabel = btn.innerHTML;
    btn.addEventListener('click', function () {
      var done = function () { btn.innerHTML = '<i class="bi bi-check2-circle"></i> Copied!'; setTimeout(function () { btn.innerHTML = defaultLabel; }, 1500); };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(rawText).then(done).catch(function () {
          var t = wrap.querySelector('textarea'); t.select(); document.execCommand('copy'); done();
        });
      } else {
        var t = wrap.querySelector('textarea'); t.select(); document.execCommand('copy'); done();
      }
    });
    box.appendChild(wrap);
  }

  function renderFields(fields, extractionId) {
    var wrap = $('[data-fields="current"]');
    wrap.innerHTML = '';

    var list = document.createElement('div');
    list.className = 'df-ocr-fields';
    wrap.appendChild(list);

    function addRow(f) {
      var row = document.createElement('div');
      row.className = 'df-ocr-field';
      row.setAttribute('data-field-id', f.field_id);
      if (state.editable) {
        row.innerHTML = '<div class="df-ocr-field-inputs">'
          + '<input class="df-input df-ocr-label" type="text" value="' + esc(f.label || '') + '" placeholder="Label" data-field-label>'
          + '<input class="df-input df-ocr-value" type="text" value="' + esc(f.value || '') + '" placeholder="Value" data-field-value></div>'
          + '<button type="button" class="df-icon-btn df-icon-btn-sm" title="Remove field" data-remove-field><i class="bi bi-trash"></i></button>';
        row.querySelector('[data-remove-field]').addEventListener('click', function () {
          if (!confirm('Remove this field?')) return;
          post('/api/field-remove.php', { extraction_id: extractionId, field_id: f.field_id }).then(function (res) {
            if (res.ok) row.remove();
          });
        });
      } else {
        row.innerHTML = '<div class="df-ocr-field-inputs df-ocr-field-readonly">'
          + '<label class="df-label">' + esc(f.label || '') + '</label>'
          + '<input class="df-input" type="text" value="' + esc(f.value || '') + '" readonly></div>';
      }
      list.appendChild(row);
    }

    (fields || []).forEach(addRow);
    if (!state.editable || !extractionId) return;

    var actions = document.createElement('div');
    actions.className = 'df-ocr-field-actions';

    var addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'df-btn df-btn-outline df-btn-sm';
    addBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add Field';
    addBtn.addEventListener('click', function () {
      post('/api/field-add.php', { extraction_id: extractionId, label: '' }).then(function (res) {
        if (!res.ok) { alert(res.error || 'Could not add a field.'); return; }
        addRow(res.field);
        var lastRow = list.lastElementChild;
        var labelInput = lastRow.querySelector('[data-field-label]');
        if (labelInput) { labelInput.focus(); labelInput.select(); }
      });
    });
    actions.appendChild(addBtn);

    var saveBtn = document.createElement('button');
    saveBtn.type = 'button';
    saveBtn.className = 'df-btn df-btn-primary df-btn-sm';
    var defaultLabel = '<i class="bi bi-check2"></i> Save Information';
    saveBtn.innerHTML = defaultLabel;
    saveBtn.addEventListener('click', function () {
      var rows = {};
      list.querySelectorAll('[data-field-id]').forEach(function (row) {
        var id = row.getAttribute('data-field-id');
        var l = row.querySelector('[data-field-label]');
        var v = row.querySelector('[data-field-value]');
        if (l && v) rows[id] = { label: l.value, value: v.value };
      });
      saveBtn.disabled = true;
      post('/api/fields-save.php', { extraction_id: extractionId, rows: JSON.stringify(rows) }).then(function (res) {
        saveBtn.disabled = false;
        if (res.ok) {
          saveBtn.innerHTML = '<i class="bi bi-check2-circle"></i> Saved';
          setTimeout(function () { saveBtn.innerHTML = defaultLabel; }, 1500);
        } else {
          alert(res.error || 'Could not save.');
        }
      });
    });
    actions.appendChild(saveBtn);
    wrap.appendChild(actions);
  }

  renderCopyBox(state.currentRawText);
  renderFields(state.currentFields, state.currentExtractionId);

  if (state.editable && !state.currentExtractionId) {
    var runBtn = document.createElement('button');
    runBtn.type = 'button';
    runBtn.className = 'df-btn df-btn-primary df-btn-block';
    runBtn.innerHTML = state.currentIsImage ? '<i class="bi bi-magic"></i> Extract Information' : '<i class="bi bi-pencil"></i> Enter Information by Hand';
    runBtn.addEventListener('click', function () {
      runBtn.disabled = true;
      runLoading(post('/api/ocr-run.php', { version_id: state.currentVersionId })).then(function (res) {
        runBtn.disabled = false;
        if (res.ok) {
          state.currentExtractionId = res.extraction_id;
          runBtn.remove();
          renderCopyBox(res.raw_text);
          renderFields(res.fields, res.extraction_id);
        } else {
          alert(res.error || 'Could not process this document.');
        }
      });
    });
    $('[data-copybox="current"]').before(runBtn);
  }

  var btnSaveVersion = $('#btnSaveVersion');
  if (btnSaveVersion) {
    btnSaveVersion.addEventListener('click', function () { document.getElementById('saveVersionForm').submit(); });
  }
  var btnSubmit = $('#btnSubmit');
  if (btnSubmit) {
    btnSubmit.addEventListener('click', function () {
      if (confirm('Submit this document for review? You will not be able to edit it until it is returned.')) {
        document.getElementById('submitReviewForm').submit();
      }
    });
  }
})();