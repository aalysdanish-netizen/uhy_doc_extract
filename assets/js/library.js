/* assets/js/library.js - filters are open on desktop, collapsed on phones */
(function () {
  'use strict';
  var filters = document.querySelector('[data-filters]');
  if (filters && window.innerWidth >= 768) filters.open = true;
})();