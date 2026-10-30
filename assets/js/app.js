/* assets/js/app.js - behaviour shared by every logged-in page */
(function () {
  'use strict';

  // Set to e.g. 30000 to refresh badges every 30 seconds. 0 = no polling.
  var POLL_MS = 0;

  var base = (document.querySelector('meta[name="df-base"]') || {}).content || '';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

  function qs(sel, ctx)  { return (ctx || document).querySelector(sel); }
  function qsa(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }

  /* ---------- Slide-out sidebar (mobile) ---------- */
  var sidebar = qs('#dfSidebar');
  var overlay = qs('.df-sidebar-overlay');

  function setSidebar(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('is-open', open);
    overlay.classList.toggle('is-open', open);
    document.body.classList.toggle('df-no-scroll', open);
  }
  qsa('[data-sidebar-open]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(true); }); });
  qsa('[data-sidebar-close]').forEach(function (b) { b.addEventListener('click', function () { setSidebar(false); }); });
  window.addEventListener('resize', function () { if (window.innerWidth >= 992) setSidebar(false); });

  /* ---------- Dropdowns (notification bell) ---------- */
  function closeDropdowns(except) {
    qsa('[data-dropdown].is-open').forEach(function (d) {
      if (d === except) return;
      d.classList.remove('is-open');
      var t = qs('[data-dropdown-toggle]', d);
      if (t) t.setAttribute('aria-expanded', 'false');
    });
  }
  qsa('[data-dropdown-toggle]').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      var dd = toggle.closest('[data-dropdown]');
      var open = !dd.classList.contains('is-open');
      closeDropdowns(dd);
      dd.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('[data-dropdown]')) closeDropdowns();
  });

  /* ---------- Modals ---------- */
  function closeModals() {
    qsa('.df-modal.is-open').forEach(function (m) { m.classList.remove('is-open'); });
    if (!sidebar || !sidebar.classList.contains('is-open')) document.body.classList.remove('df-no-scroll');
  }
  qsa('[data-modal-open]').forEach(function (b) {
    b.addEventListener('click', function () {
      var m = qs(b.getAttribute('data-modal-open'));
      if (m) { m.classList.add('is-open'); document.body.classList.add('df-no-scroll'); }
    });
  });
  qsa('[data-modal-close]').forEach(function (b) { b.addEventListener('click', closeModals); });
  qsa('.df-modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) closeModals(); });   // click the dark backdrop
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeDropdowns(); closeModals(); setSidebar(false); }
  });

  /* ---------- Badges (sidebar + bell) ---------- */
  function applyBadges(counts) {
    qsa('[data-badge]').forEach(function (el) {
      var n = parseInt(counts[el.getAttribute('data-badge')] || 0, 10);
      if (n > 0) { el.textContent = n > 99 ? '99+' : String(n); el.hidden = false; }
      else       { el.hidden = true; }
    });
  }

  function refreshBadges() {
    return fetch(base + '/api/badges.php', { credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { if (d && d.ok) applyBadges(d.counts); })
      .catch(function () { /* ignore network errors */ });
  }

  // "Mark all as read" in the bell panel
  qsa('[data-mark-all-read]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var fd = new FormData();
      fd.append('action', 'all');
      fd.append('csrf_token', csrf);
      fetch(base + '/api/mark-read.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d.ok) {
            qsa('.df-notif.is-unread').forEach(function (n) { n.classList.remove('is-unread'); });
            applyBadges(d.counts);
          }
        })
        .catch(function () {});
    });
  });

  // Refresh when the user comes back to the tab, or returns with the browser Back button
  document.addEventListener('visibilitychange', function () { if (!document.hidden) refreshBadges(); });
  window.addEventListener('pageshow', function (e) { if (e.persisted) refreshBadges(); });
  if (POLL_MS > 0) setInterval(refreshBadges, POLL_MS);

  // Available to page-specific scripts later (e.g. after the chat marks messages read)
  window.DocumentFlow = { refreshBadges: refreshBadges, applyBadges: applyBadges };
})();