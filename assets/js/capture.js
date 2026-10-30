/* assets/js/capture.js - multi-file Capture / Upload page */
(function () {
  'use strict';

  var form = document.getElementById('captureForm');
  if (!form) return;

  var MAX = parseInt(form.getAttribute('data-max-bytes'), 10) || 15728640;
  var MAX_FILES = parseInt(form.getAttribute('data-max-files'), 10) || 10;
  var ALLOWED = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];

  function $(id) { return document.getElementById(id); }
  var inCamera = $('inCamera'), inGallery = $('inGallery');
  var btnCamera = $('btnCamera'), btnGallery = $('btnGallery'), btnAddMore = $('btnAddMore'), btnUpload = $('btnUpload');
  var dzEmpty = $('dzEmpty'), fileQueue = $('fileQueue'), fileGrid = $('fileGrid'), queueCount = $('queueCount');
  var nameInput = $('document_name'), typeSelect = $('type_id');

  var queue = [];          // { file, previewUrl }
  var nameTouched = nameInput.value !== '';

  if (window.matchMedia && window.matchMedia('(pointer: fine)').matches) {
    btnGallery.className = 'df-btn df-btn-primary';
  }

  function fmtSize(b) { return b < 1048576 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1048576).toFixed(1) + ' MB'; }
  function ext(n) { var m = /\.([a-z0-9]+)$/i.exec(n || ''); return m ? m[1].toLowerCase() : ''; }

  function clearErrors() {
    form.querySelectorAll('[data-error-for]').forEach(function (el) { el.hidden = true; el.textContent = ''; });
    var a = $('formAlert'); a.hidden = true; a.textContent = '';
  }
  function showError(key, msg) {
    var el = form.querySelector('[data-error-for="' + key + '"]');
    if (el) { el.textContent = msg; el.hidden = false; }
    else { var a = $('formAlert'); a.textContent = msg; a.hidden = false; }
  }

  btnCamera.addEventListener('click', function () { inCamera.click(); });
  btnGallery.addEventListener('click', function () { inGallery.click(); });
  btnAddMore.addEventListener('click', function () { inGallery.click(); });

  inCamera.addEventListener('change', function () {
    if (inCamera.files && inCamera.files[0]) addFiles([inCamera.files[0]]);
    inCamera.value = '';
  });
  inGallery.addEventListener('change', function () {
    if (inGallery.files && inGallery.files.length) addFiles(Array.prototype.slice.call(inGallery.files));
    inGallery.value = '';
  });

  function shrinkImage(file) {
    return new Promise(function (resolve, reject) {
      if (/^image\/(jpeg|png)$/.test(file.type) && file.size < 1572864) { resolve(file); return; }
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () {
        var scale = Math.min(1, 2400 / Math.max(img.naturalWidth, img.naturalHeight));
        var c = document.createElement('canvas');
        c.width = Math.round(img.naturalWidth * scale);
        c.height = Math.round(img.naturalHeight * scale);
        c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
        URL.revokeObjectURL(url);
        c.toBlob(function (blob) {
          if (!blob) { reject(); return; }
          resolve(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.88);
      };
      img.onerror = function () { URL.revokeObjectURL(url); reject(); };
      img.src = url;
    });
  }

  function addFiles(files) {
    clearErrors();
    files.forEach(function (file) {
      if (queue.length >= MAX_FILES) { showError('files', 'You can add up to ' + MAX_FILES + ' files.'); return; }
      var e = ext(file.name);
      var isImage = /^image\//.test(file.type) || ['jpg', 'jpeg', 'png', 'heic', 'heif', 'webp'].indexOf(e) > -1;
      if (!isImage && ALLOWED.indexOf(e) === -1) { showError('files', file.name + ': file type not allowed.'); return; }
      if (file.size > MAX) { showError('files', file.name + ': too large (max ' + Math.round(MAX / 1048576) + ' MB).'); return; }

      if (isImage) {
        shrinkImage(file).then(pushToQueue).catch(function () { pushToQueue(file); });
      } else {
        pushToQueue(file);
      }
    });
  }

  function pushToQueue(file) {
    var item = { file: file, previewUrl: /^image\//.test(file.type) ? URL.createObjectURL(file) : null };
    queue.push(item);
    render();
  }

  function removeAt(idx) {
    if (queue[idx].previewUrl) URL.revokeObjectURL(queue[idx].previewUrl);
    queue.splice(idx, 1);
    render();
  }

  function render() {
    dzEmpty.hidden = queue.length > 0;
    fileQueue.hidden = queue.length === 0;
    queueCount.textContent = queue.length;

    fileGrid.innerHTML = '';
    queue.forEach(function (item, idx) {
      var card = document.createElement('div');
      card.className = 'df-file-card';
      var icons = { pdf: 'bi-file-earmark-pdf', docx: 'bi-file-earmark-word', xlsx: 'bi-file-earmark-excel' };
      var thumb = item.previewUrl
        ? '<img src="' + item.previewUrl + '" alt="">'
        : '<i class="bi ' + (icons[ext(item.file.name)] || 'bi-file-earmark') + '"></i>';
      card.innerHTML =
        '<div class="df-file-card-thumb">' + thumb + '</div>' +
        '<div class="df-file-card-meta"><small>' + escapeHtml(item.file.name) + '</small><small class="df-muted">' + fmtSize(item.file.size) + '</small></div>' +
        '<button type="button" class="df-file-card-remove" title="Remove"><i class="bi bi-x-lg"></i></button>';
      card.querySelector('.df-file-card-remove').addEventListener('click', function () { removeAt(idx); });
      fileGrid.appendChild(card);
    });
  }

  function escapeHtml(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  typeSelect.addEventListener('change', function () {
    if (!nameTouched && typeSelect.value) nameInput.value = typeSelect.options[typeSelect.selectedIndex].text;
  });
  nameInput.addEventListener('input', function () { nameTouched = true; });

  function setBusy(busy) {
    btnUpload.disabled = busy;
    btnUpload.classList.toggle('is-loading', busy);
    $('uploadStatus').hidden = !busy;
  }
  function setProgress(pct, text) { $('uploadBar').style.width = pct + '%'; $('uploadText').textContent = text; }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var bad = false;
    if (queue.length === 0) { showError('files', 'Please add at least one photo or file.'); bad = true; }
    if (!$('client_id').value) { showError('client_id', 'Please select a client.'); bad = true; }
    if (!$('type_id').value) { showError('type_id', 'Please select a document type.'); bad = true; }
    if (bad) return;

    var fd = new FormData(form);
    queue.forEach(function (item) { fd.append('files[]', item.file, item.file.name); });

    setBusy(true);
    setProgress(0, 'Uploading… 0%');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href.split('#')[0]);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.upload.onprogress = function (ev) {
      if (!ev.lengthComputable) return;
      var pct = Math.round(ev.loaded / ev.total * 100);
      setProgress(pct, pct < 100 ? 'Uploading… ' + pct + '%' : 'Saving documents…');
    };
    xhr.onload = function () {
      var data = null;
      try { data = JSON.parse(xhr.responseText); } catch (err) {}
      if (xhr.status === 200 && data && data.ok) {
        setProgress(100, 'Done. Opening your documents…');
        window.location.href = data.redirect;
        return;
      }
      setBusy(false);
      var errors = (data && data.errors) || { general: 'Something went wrong. Please try again.' };
      Object.keys(errors).forEach(function (k) { showError(k, errors[k]); });
    };
    xhr.onerror = function () { setBusy(false); showError('general', 'Network problem. Please try again.'); };
    xhr.send(fd);
  });
})();