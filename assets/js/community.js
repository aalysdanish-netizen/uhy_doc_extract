/* assets/js/community.js */
(function () {
  'use strict';
  var D = window.DFChat;

  document.querySelectorAll('.df-composer, .df-reply-form').forEach(D.wireAttachPreview);

  document.querySelectorAll('[data-chat-back]').forEach(function (btn) {
    btn.addEventListener('click', function () { document.querySelector('.df-chat-layout').classList.remove('df-chat-show-main'); });
  });
  document.querySelectorAll('.df-chat-channel').forEach(function (link) {
    link.addEventListener('click', function () {
      if (window.innerWidth < 768) document.querySelector('.df-chat-layout').classList.add('df-chat-show-main');
    });
  });

  function appendPost(post, isReply, container) {
    var atts = (post.attachments || []).map(function (a) {
      return '<a class="df-doc-chip" href="' + D.base + '/files/chat-download.php?type=community&id=' + a.attachment_id + '">' + D.esc(a.file_name) + '</a>';
    }).join('');
    var identity = '<strong>' + D.esc(post.author_name) + ' <span class="df-username">@' + D.esc(post.author_username || '') + '</span></strong>';
    var html = (isReply ? '<div class="df-thread-reply">' : '<article class="df-thread-post">')
      + '<div class="df-thread-head">' + identity + '<small>Just now</small></div>'
      + (post.body ? '<p class="df-thread-body">' + D.esc(post.body) + '</p>' : '')
      + atts + (isReply ? '</div>' : '</article>');
    container.insertAdjacentHTML(isReply ? 'beforeend' : 'afterbegin', html);
  }

  var postForm = document.getElementById('postForm');
  if (postForm) {
    postForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(postForm);
      fd.append('channel_id', postForm.getAttribute('data-channel-id'));
      D.post('/api/community-post.php', fd).then(function (res) {
        if (!res.ok) { alert(res.error || 'Could not post.'); return; }
        appendPost(res.post, false, document.getElementById('threadList'));
        postForm.reset();
        postForm.querySelector('.df-attach-list').textContent = '';
      });
    });
  }

  document.querySelectorAll('.df-reply-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var box = document.querySelector('[data-replies="' + btn.getAttribute('data-post-id') + '"]');
      if (box) box.hidden = !box.hidden;
    });
  });

  document.querySelectorAll('.df-reply-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      fd.append('channel_id', form.getAttribute('data-channel-id'));
      fd.append('parent_post_id', form.getAttribute('data-parent-id'));
      D.post('/api/community-post.php', fd).then(function (res) {
        if (!res.ok) { alert(res.error || 'Could not reply.'); return; }
        var container = form.closest('.df-thread-replies');
        appendPost(res.post, true, container);
        form.reset();
      });
    });
  });
})();