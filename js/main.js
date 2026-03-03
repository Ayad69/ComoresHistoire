(function () {
  'use strict';

  var navMenu = document.querySelector('.nav-menu');
  var navToggle = document.querySelector('.nav-toggle');

  function setAriaExpanded(open) {
    if (navToggle) {
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      navToggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    }
  }

  function closeMenu() {
    if (navMenu) {
      navMenu.classList.remove('is-open');
      setAriaExpanded(false);
    }
  }

  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      var isOpen = navMenu.classList.toggle('is-open');
      setAriaExpanded(isOpen);
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 768) closeMenu();
    });

    document.addEventListener('click', function (e) {
      if (navMenu.classList.contains('is-open') && !navMenu.contains(e.target) && !navToggle.contains(e.target)) {
        closeMenu();
      }
    });
  }

  document.querySelectorAll('.nav-menu a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var href = this.getAttribute('href');
      if (href === '#') return;
      e.preventDefault();
      var target = document.querySelector(href);
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        closeMenu();
      }
    });
  });

  document.querySelectorAll('.nav-menu a[href]').forEach(function (link) {
    if (link.getAttribute('href').indexOf('#') !== 0) {
      link.addEventListener('click', closeMenu);
    }
  });
})();
