/* assets/js/personal-chat.js */
(function () {
  'use strict';
  var D = window.DFChat;
  var layout = document.querySelector('[data-personal-chat]');
  if (!layout) return;

  document.querySelectorAll('.df-composer').forEach(D.wireAttachPreview);

  document.querySelectorAll('[data-chat-back]').forEach(function (btn) {
    btn.addEventListener('click', function () { layout.classList.remove('df-chat-show-main'); });
  });
  document.querySelectorAll('.df-chat-channel').forEach(function (link) {
    link.addEventListener('click', function () { if (window.innerWidth < 768) layout.classList.add('df-chat-show-main'); });
  });
  if (window.innerWidth < 768 && layout.getAttribute('data-open-with') !== '0') {
    layout.classList.add('df-chat-show-main');
  }

  /* ---------- Staff search (start a new conversation) ---------- */
  var search = document.getElementById('staffSearch');
  var results = document.getElementById('staffResults');
  var timer = null;
  if (search) {
    search.addEventListener('input', function () {
      clearTimeout(timer);
      var q = search.value.trim();
      timer = setTimeout(function () {
        fetch(D.base + '/api/staff-search.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (d) {
            if (!d.ok) return;
            results.innerHTML = d.items.map(function (u) {
              return '<a class="df-chat-channel" href="' + D.base + '/chat/personal.php?with=' + u.user_id + '">'
                + '<span class="df-avatar df-avatar-sm">' + D.esc(u.initials) + '</span>'
                + '<span class="df-chat-channel-name"><strong>' + D.esc(u.name) + ' <span class="df-username">@' + D.esc(u.username) + '</span></strong><small>' + D.esc(u.department || '') + '</small></span></a>';
            }).join('');
            results.hidden = d.items.length === 0;
          });
      }, 250);
    });
    search.addEventListener('blur', function () { setTimeout(function () { results.hidden = true; }, 150); });
    search.addEventListener('focus', function () { if (results.innerHTML) results.hidden = false; });
  }

  /* ---------- Sending a message ---------- */
  var form = document.getElementById('messageForm');
  var list = document.getElementById('messageList');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      fd.append('receiver_id', form.getAttribute('data-receiver-id'));
      D.post('/api/chat-send.php', fd).then(function (res) {
        if (!res.ok) { alert(res.error || 'Could not send.'); return; }
        var atts = (res.message.attachments || []).map(function (a) {
          return '<a class="df-doc-chip" href="' + D.base + '/files/chat-download.php?type=personal&id=' + a.attachment_id + '">' + D.esc(a.file_name) + '</a>';
        }).join('');
        var html = '<div class="df-bubble-row is-mine"><div class="df-bubble">'
          + (res.message.message ? '<p>' + D.esc(res.message.message) + '</p>' : '') + atts
          + '<span class="df-bubble-time">Just now</span></div></div>';
        list.insertAdjacentHTML('beforeend', html);
        list.scrollTop = list.scrollHeight;
        form.reset();
        form.querySelector('.df-attach-list').textContent = '';
        if (window.DocumentFlow) window.DocumentFlow.refreshBadges();
      });
    });
  }
  if (list) list.scrollTop = list.scrollHeight;
})();